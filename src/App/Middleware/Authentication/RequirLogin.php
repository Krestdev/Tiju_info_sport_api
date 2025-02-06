<?php

declare(strict_types=1);

namespace App\Middleware\Authentication;

use App\Db\Repository\UserService;
use App\Db\Schema\UserSchema;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Psr7\Factory\ResponseFactory;

class RequirLogin
{
  public function __construct(private UserService $user, private ResponseFactory $factory) {}
  public function __invoke(Request $request, RequestHandler $handler): Response
  {
    if (isset(($_SESSION["user_id"]))) {
      $user = $this->user->findById((int)$_SESSION["user_id"]);

      if (!$user) {
        $request = $request->withAttribute("user", $user);
        return $handler->handle($request);
      }
    }
    $response = $this->factory->createResponse();
    $response->getBody()->write("Unauthorised");
    return $response->withStatus(401);
  }
}
