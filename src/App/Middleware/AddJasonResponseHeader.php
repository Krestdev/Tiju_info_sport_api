<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ResponseInterface as Response;

class AddJasonResponseHeader
{
  public function __invoke(Request $request, RequestHandler $handler)
  {
    $response = $handler->handle($request);
    return $response->withHeader('Content-Type', 'application/json');
  }
}
