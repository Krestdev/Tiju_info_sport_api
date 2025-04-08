<?php

declare(strict_types=1);

namespace App\Middleware\Permissions;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Exception\HttpForbiddenException;

class RoleAccessControl
{
  public function adminLevelAccess(Request $request, RequestHandler $handler)
  {
    $user = $_SESSION['user'] ?? null;
    if ($user && in_array($user['role'], ['admin', 'super-admin'])) {
      return $handler->handle($request);
    }

    throw new HttpForbiddenException($request, "You are not allowed to access this resource");
  }

  public function superAdminLevelAccess(Request $request, RequestHandler $handler)
  {
    $user = $_SESSION['user'] ?? null;
    if ($user && in_array($user['role'], ['super-admin'])) {
      return $handler->handle($request);
    }

    throw new HttpForbiddenException($request, "You are not allowed to access this resource");
  }

  public function editorLevelAccess(Request $request, RequestHandler $handler)
  {
    $user = $_SESSION['user'] ?? null;
    if ($user && in_array($user['role'], ['admin', 'editor', 'super-admin'])) {
      return $handler->handle($request);
    }

    throw new HttpForbiddenException($request, "You are not allowed to access this resource");
  }
}
