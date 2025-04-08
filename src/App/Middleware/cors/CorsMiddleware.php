<?php

declare(strict_types=1);

use Google\Service\Dns\ResponseHeader;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ResponseInterface as Response;

// $app->options('/{routes:.+}', function (Request $request, Response $response, string $args) {
//   return $response;
// });

$app->add(function (Request $request, $handler) {
  // Bypass for preflight
  if ($request->getMethod() === 'OPTIONS') {
    $response = $handler->handle($request);
    return $response
      ->withHeader('Access-Control-Allow-Origin', '*')
      ->withStatus(204);
  }

  // Normal requests
  $response = $handler->handle($request);
  return $response
    ->withHeader('Access-Control-Allow-Origin', '*')
    ->withHeader('Access-Control-Allow-Methods', '*')
    ->withHeader('Access-Control-Expose-Headers', '*');
});
