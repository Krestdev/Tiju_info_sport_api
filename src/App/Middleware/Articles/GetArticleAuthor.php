<?php

declare(strict_types=1);

namespace App\Middleware\Articles;

use App\Db\Repository\CategoryService;
use App\Db\Repository\UserService;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpNotFoundException;
use Slim\Routing\RouteContext;

class GetArticleAuthor
{
  public function __construct(private UserService $userService, private CategoryService $categoryService) {}

  public function __invoke(Request $request, RequestHandler $handler)
  {
    $id = $request->getParsedBody()['user_id'] ?? null;

    if (!isset($id)) {
      throw new HttpNotFoundException($request, 'Article Author required');
    }

    $author = $this->userService->findById((int)$id);

    if ($author === null) {
      throw new HttpNotFoundException($request, "Author user not found");
    }

    $category_id = $request->getParsedBody()['category_id'] ?? null;

    if (!isset($category_id)) {
      throw new HttpNotFoundException($request, 'Article category required');
    }

    $category = $this->categoryService->findById((int)$category_id);

    if ($category === null) {
      throw new HttpNotFoundException($request, "Article category not found");
    }

    $request = $request->withAttribute('author', $author);
    $request = $request->withAttribute('category', $category);

    return $handler->handle($request);
  }
}
