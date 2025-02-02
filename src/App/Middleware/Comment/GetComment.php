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

class GetComment
{
  public function __construct(private CommentService $commentService) {}

  public function __invoke(Request $request, RequestHandler $handler)
  {
    $context = RouteContext::fromRequest($request);
    $route = $context->getRoute();
    $id = $route->getArgument('comment_id');

    $comment = $this->commentService->findById((int)$id);

    if ($comment === null) {
      throw new HttpNotFoundException($request, "Comment not found");
    }

    $request = $request->withAttribute('comment', $comment);

    return $handler->handle($request);
  }
}
