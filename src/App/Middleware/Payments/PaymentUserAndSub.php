<?php

declare(strict_types=1);

namespace App\Middleware\Payments;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use App\Db\Repository\PaymentService;
use App\Db\Repository\SubscriptionService;
use App\Db\Repository\UserService;
use Slim\Exception\HttpNotFoundException;
use Slim\Psr7\Factory\ResponseFactory;
use Psr\Http\Message\ResponseInterface as Response;

class PaymentUserAndSub
{
  public function __construct(private ResponseFactory $factory, private SubscriptionService $subscriptionService, private UserService $userService) {}

  public function __invoke(Request $request, RequestHandler $handler)
  {
    $body = $request->getParsedBody();
    $subscription_id = $body["subscription_id"];
    $user_id = $body["user_id"];

    if (!isset($subscription_id)) {
      throw new HttpNotFoundException($request, "'subscription_id' Subscription required");
    }

    if (!isset($user_id)) {
      throw new HttpNotFoundException($request, " 'user_id' Payment Customer required");
    }

    $subscription = $this->subscriptionService->findById((int)$subscription_id);
    $user = $this->userService->findById((int)$user_id);

    if ($user === null) {
      throw new HttpNotFoundException($request, "User Not found");
    }

    if ($subscription === null) {
      throw new HttpNotFoundException($request, "Subscription Not found");
    }

    if ($subscription->getCustomer()->getId() !== $user->getId()) {
      throw new HttpNotFoundException($request, "This subscription is not yours");
    }

    if (in_array($subscription->getStatus(), ["ACCEPTED", "CREATING", "COMPLETED", "ENQUEUED", "SUBMITED"])) {
      $response = $this->factory->createResponse();

      $response->getBody()->write(json_encode([
        "Subscription" => $subscription,
        "message" => "Subscription is " . $subscription->getStatus()
      ]));

      return $response;
    }

    $request = $request->withAttribute("customer", $user);
    $request = $request->withAttribute("subscription", $subscription);

    // var_dump(in_array($subscription->getStatus(), ["PAYED", "CREATING", "PENDING"]));
    return $handler->handle($request);
  }
}
