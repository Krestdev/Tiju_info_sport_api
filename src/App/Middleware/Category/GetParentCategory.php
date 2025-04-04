<?php

declare(strict_types=1);

namespace App\Middleware\Category;

use App\Db\Repository\CategoryService;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpNotFoundException;
use Slim\Routing\RouteContext;

class GetParentCategory
{
  public function __construct(private CategoryService $categoryService) {}

  public function __invoke(Request $request, RequestHandler $handler)
  {
    $context = RouteContext::fromRequest($request);
    $route = $context->getRoute();
    $id = $route->getArgument('parent_id');

    $comment = $this->categoryService->findById((int)$id);

    if ($comment === null) {
      throw new HttpNotFoundException($request, "Parent Category not found");
    }

    $request = $request->withAttribute('parentCategory', $comment);

    return $handler->handle($request);
  }
}
