<?php

declare(strict_types=1);

namespace App\Middleware\Package;

use App\Db\Repository\PackageService;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Exception\HttpNotFoundException;
use Slim\Routing\RouteContext;

class GetPackage
{
  public function __construct(private PackageService $packageService) {}

  public function __invoke(Request $request, RequestHandler $handler)
  {
    $context = RouteContext::fromRequest($request);
    $route = $context->getRoute();
    $id = $route->getArgument('package_id');

    $package = $this->packageService->findById((int)$id);

    if ($package === null) {
      throw new HttpNotFoundException($request, "package not found");
    }

    $request = $request->withAttribute('package', $package);

    return $handler->handle($request);
  }
}
