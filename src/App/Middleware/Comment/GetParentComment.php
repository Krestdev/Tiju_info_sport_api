<?php

declare(strict_types=1);

namespace App\Middleware\Comment;

use App\Db\Repository\CommentService;
use App\Db\Repository\UserService;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpNotFoundException;
use Slim\Routing\RouteContext;

class GetParentComment
{
  public function __construct(private CommentService $commentService) {}

  public function __invoke(Request $request, RequestHandler $handler)
  {
    $context = RouteContext::fromRequest($request);
    $route = $context->getRoute();
    $id = $route->getArgument('id');

    $comment = $this->commentService->findById((int)$id);

    if ($comment === null) {
      throw new HttpNotFoundException($request, "Parent comment not found");
    }

    $request = $request->withAttribute('parentComment', $comment);

    return $handler->handle($request);
  }
}
