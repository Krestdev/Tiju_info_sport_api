<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Db\Repository\UserService;
use App\Mail\SendMail;
use DateTimeImmutable;
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
      'role' => ['required', ['in', ["admin", "user", "editor", "super-admin"]],],
    ]);
  }

  public function show(Request $request, Response $response, string $user_id): Response
  {
    $user = $request->getAttribute('user');

    // $encription_key = Key::loadFromAsciiSafeString($_ENV['ENCRYPTION_KEY']);
    // $api_key = Crypto::encrypt($user->getApiKey(), $encription_key);
    $response->getBody()->write(json_encode([
      "user" => $user,
      // "api key" => $api_key
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

    if ($user !== null && !$user->getVerified()) {
      $expired = $user->getVerificationTokenExpireAt() < new DateTimeImmutable();
      $token = $expired ? $this->userService->generateVerificationToken($user->getId()) : $user->getVerificationToken();

      $this->mailSender->send($user->getEmail(), $user->getUsername(), "Email verification", "Click here to verify your email: https://www.tyjuinfosport.com/recuperation-mot-de-passe?token=" . $token);
    }

    if ($user !== null) {
      throw new HttpNotFoundException($request, "email address taken");
    }

    $data["password"] = password_hash($data["password"], PASSWORD_DEFAULT);

    $data["role"] = "user";

    // if ($data["role"] === "super-admin") {
    //   $api_key = bin2hex(random_bytes(16));

    //   $encryption_key = key::loadFromAsciiSafeString($_ENV["ENCRYPTION_KEY"]);

    //   $data["api-key"] = Crypto::encrypt($api_key, $encryption_key);

    //   // $data["api-key"] = $api_key;
    //   $data['api-key-hash'] = hash_hmac('sha256', $api_key, $_ENV["HASH_SECRET_KEY"]);
    // }

    $user = $this->userService->signUp($data);

    // Generate unique reset token
    $resetToken = $this->userService->generateVerificationToken($user->getId());

    // send verification mail
    $resetLink = join(["https://www.tyjuinfosport.com/recuperation-mot-de-passe?token=", $resetToken]);
    $this->mailSender->send($user->getEmail(), $user->getUsername(), "Email verification", join(["Click here to verify your email: ", $resetLink]));

    $response->getBody()->write(json_encode($user));
    return $response;
  }

  public function createUser(Request $request, Response $response): Response
  {
    $data = $request->getParsedBody();

    // validate the data
    $this->validator = $this->validator->withData($data);
    if (!$this->validator->validate()) {
      $response->getBody()->write(json_encode($this->validator->errors()));
      return $response->withStatus(422);
    }

    $user = $this->userService->findbyEmail($data['email']);

    if ($user !== null && !$user->getVerified()) {
      $expired = $user->getVerificationTokenExpireAt() < new DateTimeImmutable();
      $token = $expired ? $this->userService->generateVerificationToken($user->getId()) : $user->getVerificationToken();

      $this->mailSender->send($user->getEmail(), $user->getUsername(), "Email verification", "Click here to verify your email: https://www.tyjuinfosport.com/recuperation-mot-de-passe?token=" . $token);
    }

    if ($user !== null) {
      throw new HttpNotFoundException($request, "email address taken");
    }

    $data["password"] = password_hash($data["password"], PASSWORD_DEFAULT);

    $user = $this->userService->signUp($data);

    // Generate unique reset token
    $resetToken = $this->userService->generateVerificationToken($user->getId());

    // send verification mail

    $resetLink = join(["https://www.tyjuinfosport.com/recuperation-mot-de-passe?token=", $resetToken]);
    $this->mailSender->send($user->getEmail(), $user->getUsername(), "Email verification", join(["Click here to verify your email: ", $resetLink]));

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

  public function changeRole(Request $request, Response $response, string $user_id): Response
  {
    $data = $request->getParsedBody();

    $rolechangeValidator = new Validator($data);
    $rolechangeValidator->mapFieldsRules([
      'from_role' => ['required', ['in', ["admin", "user", "editor", "super-admin"]],],
      'to_role' => ['required', ['in', ["admin", "user", "editor", "super-admin"]],],
      'target_user_id' => ['required', 'integer'],
    ]);

    // validate the data
    $validated = $rolechangeValidator->withData($data);
    if (!$validated->validate()) {
      $response->getBody()->write(json_encode($validated->errors()));
      return $response->withStatus(422);
    }

    // Same Role
    if ($data['from_role'] === $data['to_role']) {
      throw new HttpNotFoundException($request, "You cannot change to the same role");
    }

    // Cannot change an admin to super-admin or super-admin to admin
    if ($data['from_role'] === "super-admin" && $data['to_role'] === "admin") {
      throw new HttpNotFoundException($request, "You cannot change to admin");
    }
    if ($data['from_role'] === "super-admin" && $data['to_role'] === "editor") {
      throw new HttpNotFoundException($request, "You cannot change to editor");
    }
    if ($data['from_role'] === "super-admin" && $data['to_role'] === "user") {
      throw new HttpNotFoundException($request, "You cannot change to editor");
    }

    if ($data['from_role'] === "admin" && $data['to_role'] === "super-admin") {
      throw new HttpNotFoundException($request, "You cannot change to super-admin");
    }
    if ($data['from_role'] === "editor" && $data['to_role'] === "super-admin") {
      throw new HttpNotFoundException($request, "You cannot change to super-admin");
    }
    if ($data['from_role'] === "user" && $data['to_role'] === "super-admin") {
      throw new HttpNotFoundException($request, "You cannot change to super-admin");
    }

    // Cannot change a admin to user or editor
    if ($data['from_role'] === "admin" && $data['to_role'] === "user") {
      throw new HttpNotFoundException($request, "You cannot change to user");
    }

    $user = $this->userService->changeRole((int)$data['target_user_id'], $data['to_role']);
    $response->getBody()->write(json_encode($user));
    return $response;
  }

  public function requestPasswordReset(Request $request, Response $response): Response
  {
    $data = $request->getParsedBody();

    $validatorLogin = new Validator($data);

    $validatorLogin->mapFieldsRules([
      'email' => ['required', 'email'],
    ]);

    $validatorLogin = $validatorLogin->withData($data);
    if (!$validatorLogin->validate()) {
      $response->getBody()->write(json_encode($validatorLogin->errors()));
      return $response->withStatus(422);
    }

    $user = $this->userService->findbyEmail($data['email']);

    if ($user === null) {
      throw new HttpNotFoundException($request, "Email not found");
    }

    // Generate unique reset token
    $resetToken = $this->userService->generateResetToken($user->getId());

    // send mail with reset link
    $resetLink = join(["https://www.tyjuinfosport.com/recuperation-mot-de-passe?token=", $resetToken]);
    $this->mailSender->send($user->getEmail(), $user->getUsername(), "Password Reset Request", join(["Click here to reset your password: ", $resetLink]));
    // send verification mail
    $response->getBody()->write(json_encode($user));
    return $response;
  }

  public function validateToken(Request $request, Response $response): Response
  {

    $data = $request->getParsedBody();
    $user = $this->userService->validateResetToken($data["token"]);
    if ($user === null) {
      throw new HttpNotFoundException($request, "No Token Found");
    }

    if ($user->getResetToken() !== null && new DateTimeImmutable() < $user->getResetTokenExpireAt()) {
      $response->getBody()->write(json_encode('Token is valid'));
      return $response;
    }

    throw new HttpNotFoundException($request, "Token not valid");
  }

  public function verifyEmail(Request $request, Response $response, string $token): Response
  {
    $user = $this->userService->validateResetToken($token);
    if ($user === null) {
      throw new HttpNotFoundException($request, "No Token Found");
    }

    if ($user->getVerificationToken() !== null && new DateTimeImmutable() < $user->getResetTokenExpireAt()) {
      $this->userService->verifyEmail($user);
      $response->getBody()->write(json_encode(['message' => 'Token is valid', 'user' => $user]));
      return $response;
    }

    throw new HttpNotFoundException($request, "Token not valid");
  }

  public function resetPassword(Request $request, Response $response): Response
  {
    $data = $request->getParsedBody();

    $validatorNewPassword = new Validator($data);

    $validatorNewPassword->mapFieldsRules([
      'password' => ['required', ['lengthMin', 8]],
      'token' => ['required']
    ]);


    $validatorNewPassword = $validatorNewPassword->withData($data);
    if (!$validatorNewPassword->validate()) {
      $response->getBody()->write(json_encode($validatorNewPassword->errors()));
      return $response->withStatus(422);
    }

    if (!$data['token'] || !$data['password']) {
      throw new HttpNotFoundException($request, "Token or new password not provided");
    }

    $user = $this->userService->findByToken($data['token']);

    if (!$user || !$user->isResetTokenValid()) {
      $response->getBody()->write(json_encode('Invalid or expired token'));
      return $response->withStatus(400);
    }

    // Hash the new password
    $user = $this->userService->resetPassword($user, $data["password"]);

    $response->getBody()->write(json_encode($user));
    return $response;
  }

  public function edit(Request $request, Response $response, string $user_id): Response
  {
    $data = $request->getParsedBody();

    $validator = new Validator($data);
    $validator->mapFieldsRules([
      'name' => [['lengthMin', 2]],
      'email' => ['email'],
      'password' => [['lengthMin', 8]]
    ]);

    // validate the data
    $validator = $validator->withData($data);
    if (!$validator->validate()) {
      $response->getBody()->write(json_encode($validator->errors()));
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
