<?php

declare(strict_types=1);

namespace App\Middleware\Subscription;

use App\Db\Repository\SubscriptionService;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Exception\HttpNotFoundException;
use Slim\Routing\RouteContext;

class GetSubscription
{
  public function __construct(private SubscriptionService $subscriptionService) {}

  public function __invoke(Request $request, RequestHandler $handler)
  {
    $context = RouteContext::fromRequest($request);
    $route = $context->getRoute();
    $id = $route->getArgument('subscription_id');

    $subscription = $this->subscriptionService->findById((int)$id);

    if ($subscription === null) {
      throw new HttpNotFoundException($request, "Subscription not found");
    }

    $request = $request->withAttribute('subscription', $subscription);

    return $handler->handle($request);
  }
}
