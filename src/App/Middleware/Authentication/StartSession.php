<?php

declare(strict_types=1);

namespace App\Middleware\Authentication;

use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class StartSession
{
  public function __invoke(Request $request, RequestHandler $handler): Response
  {
    if (session_status() !== PHP_SESSION_ACTIVE) {
      session_start();
    }

    $response = $handler->handle($request);
    return $response;
  }
}
