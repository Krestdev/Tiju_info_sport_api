<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Db\Repository\UserService;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class UserIndex
{
  public function __construct(private UserService $userService) {}

  public function __invoke(Request $request, Response $response): Response
  {
    $user = $this->userService->readAll();
    $response->getBody()->write(json_encode($user));
    return $response;
  }
}
