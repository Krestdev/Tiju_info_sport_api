<?php

declare(strict_types=1);

namespace App\Middleware\Ads;

use App\Db\Repository\CategoryService;
use App\Db\Repository\UserService;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpNotFoundException;
use Slim\Routing\RouteContext;

class GetAdsAuthor
{
  public function __construct(private UserService $userService, private CategoryService $categoryService) {}

  public function __invoke(Request $request, RequestHandler $handler)
  {
    $id = $request->getParsedBody()['user_id'] ?? null;

    if (!isset($id)) {
      throw new HttpNotFoundException($request, 'Ads Author required');
    }

    $author = $this->userService->findById((int)$id);

    if ($author === null) {
      throw new HttpNotFoundException($request, "Author user not found");
    }

    $request = $request->withAttribute('author', $author);

    return $handler->handle($request);
  }
}
