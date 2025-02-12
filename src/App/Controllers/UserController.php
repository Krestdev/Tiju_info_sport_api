<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Db\Repository\UserService;
use App\Mail\SendMail;
use Google\Service\Oauth2;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpNotFoundException;
use Valitron\Validator;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;
use Exception;
use Google\Client;

class UserController
{
  public function __construct(private UserService $userService, private SendMail $mailSender, private Validator $validator)
  {
    $this->mailSender = new $mailSender();
    $this->validator->mapFieldsRules([
      'name' => ['required', ['lengthMin', 2]],
      'nick_name' => ['required', ['lengthMin', 2]],
      'email' => ['required', 'email',],
      'phone' => ['required', ['lengthMin', 2]],
      'sex' => ['required', ['lengthMin', 1]],
      'town' => ['required', ['lengthMin', 2]],
      'country' => ['required', ['lengthMin', 2]],
      'photo' => ['required', ['lengthMin', 2]],
      'password' => ['required', ['lengthMin', 8]],
      "confirm_password" => ["optional", ["equals", "password"]],
      'role' => ['required', ['lengthMin', 2]],
    ]);
  }

  public function show(Request $request, Response $response, string $user_id): Response
  {
    $user = $request->getAttribute('user');

    $encription_key = Key::loadFromAsciiSafeString($_ENV['ENCRYPTION_KEY']);
    $api_key = Crypto::encrypt($user->getApiKey(), $encription_key);
    $response->getBody()->write(json_encode([
      "user" => $user,
      "api key" => $api_key
    ]));
    return $response;
  }

  public function signup(Request $request, Response $response): Response
  {
    $data = $request->getParsedBody();

    // validate the data
    $this->validator = $this->validator->withData($data);
    if (!$this->validator->validate()) {
      $response->getBody()->write(json_encode($this->validator->errors()));
      return $response->withStatus(422);
    }

    $user = $this->userService->findbyEmail($data['email']);

    if ($user !== null) {
      throw new HttpNotFoundException($request, "email address taken");
    }

    $data["password"] = password_hash($data["password"], PASSWORD_DEFAULT);

    if ($data["role"] === "admin") {
      $api_key = bin2hex(random_bytes(16));

      $encryption_key = key::loadFromAsciiSafeString($_ENV["ENCRYPTION_KEY"]);

      $data["api-key"] = Crypto::encrypt($api_key, $encryption_key);

      // $data["api-key"] = $api_key;
      $data['api-key-hash'] = hash_hmac('sha256', $api_key, $_ENV["HASH_SECRET_KEY"]);
    }

    $user = $this->userService->signUp($data);
    // send verification mail
    $response->getBody()->write(json_encode($user));
    return $response;
  }

  public function signIn(Request $request, Response $response): Response
  {
    $data = $request->getParsedBody();

    $validatorLogin = new Validator($data);

    $validatorLogin->mapFieldsRules([
      'email' => ['required', 'email'],
      'password' => ['required']
    ]);

    $validatorLogin = $validatorLogin->withData($data);
    if (!$validatorLogin->validate()) {
      $response->getBody()->write(json_encode($validatorLogin->errors()));
      return $response->withStatus(422);
    }

    $user = $this->userService->signIn($data);

    if ($user === null || !password_verify($data["password"], $user->getPassword())) {
      throw new HttpNotFoundException($request, "Email or password not correct");
    }

    $_SESSION['user_id'] = $user->getId();

    $response->getBody()->write(json_encode($user));
    return $response;
  }

  /**
   * This method creates a new user provided the email and password
   * @param Request $request
   * @param Response $response
   * @param string $id
   * @return Response
   */
  public function edit(Request $request, Response $response, string $user_id): Response
  {
    $data = $request->getParsedBody();

    // validate the data
    $this->validator = $this->validator->withData($data);
    if (!$this->validator->validate()) {
      $response->getBody()->write(json_encode($this->validator->errors()));
      return $response->withStatus(422);
    }

    $user = $this->userService->update((int)$user_id, $data);
    $response->getBody()->write(json_encode($user));
    return $response;
  }

  public function delete(Request $request, Response $response, string $user_id): Response
  {
    $user = $this->userService->delete((int)$user_id);
    $response->getBody()->write(json_encode($user));
    return $response;
  }

  public function logout(Request $request, Response $response): Response
  {
    session_destroy();
    return $response->withStatus(302);
  }

  # google login
  public function getGoogleUri(Request $request, Response $response): Response
  {
    $client = new Client;
    $client->setClientId($_ENV["CLIENT_ID"]);
    $client->setClientSecret($_ENV["CLIENT_SECRER"]);
    $client->setRedirectUri($_ENV["REDIRECTURI"]);

    $client->addScope("email");
    $client->setScopes("profile");

    $url = $client->createAuthUrl();

    $response->getBody()->write(json_encode($url));

    return $response;
  }

  // To be completed
  public function storeGoogleUser(Request $request, Response $response)
  {
    $client = new Client;
    $client->setClientId($_ENV["CLIENT_ID"]);
    $client->setClientSecret($_ENV["CLIENT_SECRER"]);
    $client->setRedirectUri($_ENV["REDIRECTURI"]);

    $code = $request->getQueryParams()["code"];
    if (!isset($code)) {
      exit("Login Failed");
    }

    $token = $client->fetchAccessTokenWithAuthCode($code);

    $client->setAccessToken($token["access_token"]);
    $oauth = new Oauth2($client);

    $userinfo = $oauth->userinfo->get();

    $data = [
      'name' => $userinfo->getName(),
      'nick_name' => $userinfo->getGivenName() ?? $userinfo->getName(),
      'email' => $userinfo->getEmail() ?? "noemail@gmail.com",
      'password' => 'none',
      'sex' => $userinfo->getGender() ?? "M/F",
      'town' => "Town",
      'country' => $userinfo->getLocale() ?? "Country",
      'phone' => 'none',
      'verif email' => $userinfo->getVerifiedEmail(),
      'role' => 'user',
      'google_id' => $userinfo->getId()
    ];

    $user = $this->userService->findByGoogleId($userinfo->getId());
    if ($user) {
      $response->getBody()->write(json_encode($user));
      return $response;
    }

    $user = $this->userService->signUp($data);

    $response->getBody()->write(json_encode($user));
    return $response;
  }

  // Sending mail should be handled as a background process 
  // spatie/async v 1.7.0 require php ^8.3
  // exploring new solutions
  // functionality not working for now
  public function sendMail(Request $request, Response $response): Response
  {
    try {
      $this->mailSender->send("kenfackjordanjunior@gmail.com", "Jordan tiju", "mail test", "Hello");
    } catch (Exception $e) {
      $response->getBody()->write("Error sending mail");
    }

    return $response;
  }
}
