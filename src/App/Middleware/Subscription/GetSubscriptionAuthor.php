<?php

declare(strict_types=1);

namespace App\Middleware\Subscription;

use App\Db\Repository\CategoryService;
use App\Db\Repository\PackageService;
use App\Db\Repository\UserService;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpNotFoundException;
use Slim\Routing\RouteContext;

class GetSubscriptionAuthor
{
  public function __construct(private UserService $userService, private PackageService $packageService) {}

  public function __invoke(Request $request, RequestHandler $handler)
  {
    $id = $request->getParsedBody()['user_id'] ?? null;

    if (!isset($id)) {
      throw new HttpNotFoundException($request, 'Subscription Author required');
    }

    $author = $this->userService->findById((int)$id);

    if ($author === null) {
      throw new HttpNotFoundException($request, "Author user not found");
    }

    $package_id = $request->getParsedBody()['package_id'] ?? null;

    if (!isset($package_id)) {
      throw new HttpNotFoundException($request, 'Subscription Package required');
    }

    $package = $this->packageService->findById((int)$package_id);

    if ($package === null) {
      throw new HttpNotFoundException($request, "Subscription Package not found");
    }

    $request = $request->withAttribute('package', $package);
    $request = $request->withAttribute('author', $author);

    return $handler->handle($request);
  }
}
