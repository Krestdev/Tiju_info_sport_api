<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Db\Repository\UserService;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpNotFoundException;
use Valitron\Validator;

class UserController
{
  public function __construct(private UserService $userService, private Validator $validator)
  {
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
      'role' => ['required', ['lengthMin', 2]],
    ]);
  }

  public function show(Request $request, Response $response, string $id): Response
  {
    $user = $request->getAttribute('user');
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

    $user = $this->userService->signUp($data);
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
      echo json_encode($validatorLogin->errors());
      $response->getBody()->write(json_encode($validatorLogin->errors()));
      return $response->withStatus(422);
    }

    $user = $this->userService->signIn(...array_values($data));

    if ($user === null) {
      throw new HttpNotFoundException($request, "Email or password not correct");
    }

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
  public function edit(Request $request, Response $response, string $id): Response
  {
    $data = $request->getParsedBody();

    // validate the data
    $this->validator = $this->validator->withData($data);
    if (!$this->validator->validate()) {
      $response->getBody()->write(json_encode($this->validator->errors()));
      return $response->withStatus(422);
    }

    $user = $this->userService->update((int)$id, $data);
    $response->getBody()->write(json_encode($user));
    return $response;
  }

  public function delete(Request $request, Response $response, string $id): Response
  {
    $user = $this->userService->delete((int)$id);
    $body = json_encode([
      'message' => 'User deleted successfully',
      'user' => [
        "email" => $user->getEmail(),
        "createdAt" => $user->getCreatedAtFormatted()
      ]
    ]);
    $response->getBody()->write($body);
    return $response;
  }
}
