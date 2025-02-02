<?php

declare(strict_types=1);

namespace App\Middleware\Comment;

use App\Db\Repository\UserService;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpNotFoundException;
use Slim\Routing\RouteContext;

class IdentifyUser
{
  public function __construct(private UserService $userService) {}

  public function __invoke(Request $request, RequestHandler $handler)
  {
    $id = $request->getParsedBody()['user_id'] ?? null;

    $user = $this->userService->findById((int)$id);

    if ($user === null) {
      throw new HttpNotFoundException($request, "User not found");
    }

    $request = $request->withAttribute('user', $user);

    return $handler->handle($request);
  }
}
