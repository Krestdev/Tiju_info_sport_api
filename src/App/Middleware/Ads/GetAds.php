<?php

declare(strict_types=1);

namespace App\Middleware\Ads;

use App\Db\Repository\AdsService;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Exception\HttpNotFoundException;
use Slim\Routing\RouteContext;

class GetAds
{
  public function __construct(private AdsService $adsService) {}

  public function __invoke(Request $request, RequestHandler $handler)
  {
    $context = RouteContext::fromRequest($request);
    $route = $context->getRoute();
    $id = $route->getArgument('advertisement_id');

    $article = $this->adsService->findById((int)$id);

    if ($article === null) {
      throw new HttpNotFoundException($request, "advertisement not found");
    }

    $request = $request->withAttribute('advertisement', $article);

    return $handler->handle($request);
  }
}
