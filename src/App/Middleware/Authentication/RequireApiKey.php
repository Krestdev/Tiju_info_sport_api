<?php

declare(strict_types=1);

namespace App\Middleware\Authentication;

use App\Db\Repository\UserService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Psr7\Factory\ResponseFactory;

class RequireApiKey
{
  public function __construct(private ResponseFactory $factory, private UserService $userService) {}
  public function __invoke(Request $request, RequestHandler $handler): Response
  {
    // $params = $request->getQueryParams();

    // if (! array_key_exists("api-key", $params)) {
    if (! $request->hasHeader("X-API-KEY")) {
      $response = $this->factory->createResponse();
      $response->getBody()->write(json_encode("api-key missing from request"));

      return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }

    // if ($params["api-key"] !== "abc123") {
    $apiKey = $request->getHeaderLine("X-API-KEY");
    $user = $this->userService->findeApiKey($apiKey);

    if ($user) {
      $response = $this->factory->createResponse();
      $response->getBody()->write(json_encode("Your Api-Key is invalide or has Expired contact support team for assitance"));

      return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
    }

    $response = $handler->handle($request);
    return $response;
  }
}
