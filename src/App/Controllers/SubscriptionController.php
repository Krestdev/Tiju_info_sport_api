<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Db\Repository\SubscriptionService;
use DateTimeImmutable;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Valitron\Validator;

class SubscriptionController
{
  public function __construct(private SubscriptionService $subscriptionService, private Validator $validator)
  {
    $this->validator->mapFieldsRules([
      'user_id' => ['required', ['lengthMin', 1]],
      'package_id' => ['required', ['lengthMin', 1]],
    ]);
  }

  public function showAll(Request $request, Response $response): Response
  {
    $data = $this->subscriptionService->readAll();
    $response->getBody()->write(json_encode($data));
    return $response;
  }

  public function show(Request $request, Response $response, string $subscription_id): Response
  {
    $subscription = $request->getAttribute('subscription');
    $response->getBody()->write(json_encode($subscription));
    return $response;
  }

  public function create(Request $request, Response $response): Response
  {
    $data = $request->getParsedBody();

    $this->validator = $this->validator->withData($data);
    if (! $this->validator->validate()) {
      $response->getBody()->write(json_encode($this->validator->errors()));
      return $response->withStatus(422);
    }

    $user = $request->getAttribute('author');
    $package = $request->getAttribute('package');

    $data["expires_on"] = 'now';

    $subscription = $this->subscriptionService->create($user, $package, $data);
    $response->getBody()->write(json_encode($subscription));
    return $response;
  }

  public function update(Request $request, Response $response, string $subscription_id): Response
  {
    $data = $request->getParsedBody();

    $this->validator = $this->validator->withData($data);
    if (! $this->validator->validate()) {
      $response->getBody()->write(json_encode($this->validator->errors()));
      return $response->withStatus(422);
    }

    $data["expires_on"] = new DateTimeImmutable($data["expires_on"]);

    $subscription = $this->subscriptionService->update((int)$subscription_id, $data);
    $response->getBody()->write(json_encode($subscription));
    return $response;
  }

  public function delete(Request $request, Response $response, string $subscription_id): Response
  {
    $subscription = $this->subscriptionService->delete((int)$subscription_id);
    $response->getBody()->write(json_encode($subscription));
    return $response;
  }
}
