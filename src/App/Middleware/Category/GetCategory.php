<?php

declare(strict_types=1);

namespace App\Middleware\Category;

use App\Db\Repository\CategoryService;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Exception\HttpNotFoundException;
use Slim\Routing\RouteContext;

class GetCategory
{
  public function __construct(private CategoryService $categoryService) {}

  public function __invoke(Request $request, RequestHandler $handler)
  {
    $context = RouteContext::fromRequest($request);
    $route = $context->getRoute();
    $id = $route->getArgument('category_id');

    $category = $this->categoryService->findById((int)$id);

    if ($category === null) {
      throw new HttpNotFoundException($request, "category not found");
    }

    $request = $request->withAttribute('category', $category);

    return $handler->handle($request);
  }
}
