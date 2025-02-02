<?php

declare(strict_types=1);

namespace App\Middleware\Articles;

use App\Db\Repository\ArticleService;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Exception\HttpNotFoundException;
use Slim\Routing\RouteContext;

class GetArticle
{
  public function __construct(private ArticleService $articleService) {}

  public function __invoke(Request $request, RequestHandler $handler)
  {
    $context = RouteContext::fromRequest($request);
    $route = $context->getRoute();
    $id = $route->getArgument('article_id');

    $article = $this->articleService->findById((int)$id);

    if ($article === null) {
      throw new HttpNotFoundException($request, "article not found");
    }

    $request = $request->withAttribute('article', $article);

    return $handler->handle($request);
  }
}
