<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Db\Repository\PaymentService;
use App\Db\Repository\SubscriptionService;
use App\Db\Repository\UserService;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Ramsey\Uuid\Uuid;
use Valitron\Validator;

class PaymentController
{
  public function __construct(private PaymentService $payment, private UserService $user, private SubscriptionService $subscription, private Validator $validator)
  {
    $this->validator->mapFieldsRules([
      "user_id" => ["required", "numeric"],
      "subscription_id" => ["required", "numeric"],
      "method" => ["required", ["in", ["MTN", "ORG"]]],
      "phone" => ["required", "numeric", ["length", 9]]
    ]);
  }

  public function makePayment(Request $request, Response $response): Response
  {
    $user = $request->getAttribute("customer");
    $subscription = $request->getAttribute("subscription");

    $data = $request->getParsedBody();
    // validate the data
    $this->validator = $this->validator->withData($data);
    if (!$this->validator->validate()) {
      $response->getBody()->write(json_encode($this->validator->errors()));

      return $response->withStatus(422);
    }

    $data["payout_id"] = Uuid::uuid4()->toString();
    $data["correspondent"] = $data["method"] == "MTN" ? "MTN_MOMO_CMR" : "ORANGE_CMR";
    $data["address"] = $data["phone"];
    $data["country"] = $data["country"] ?? "CMR";
    $data["metadata"] = json_encode([
      [
        "fieldName" => "subscriptionId", // orderId,
        "fieldValue" => $subscription->getId() // 
      ],
      [
        "fieldName" => "customerId", // customerId,
        "fieldValue" => $user->getId(), // customer@email.com,
      ]
    ]);
    $payment = $this->payment->makePayment($user, $subscription, $data);

    $response->getBody()->write(json_encode($payment));

    return $response;
  }

  public function checkpaymentStatus(Request $request, Response $response, string $payment_id): Response
  {
    $payment = $request->getAttribute("payment");
    $response = $this->payment->checkPaymentStatus($payment);
    return $response;
  }

  public function getPaymentById(Request $request, Response $response, string $payment_id): Response
  {
    $payment = $request->getAttribute("payment");
    $response->getBody()->write(json_encode($payment));
    return $response;
  }

  public function retryPayment(Request $request, Response $response, string $payment_id): Response
  {
    $payment = $request->getAttribute("payment");

    $responseMessage = "payment request sent";

    if (in_array($payment->getStatus(), ["COMPLETED", "FAILED"])) {
      $responseMessage = "This payment was " . $payment->getStatus();
      $response->getBody()->write(json_encode($responseMessage));
      return $response;
    }
    $response = $this->payment->sendPaymentRequest($payment);
    $response->getBody()->write(json_encode($responseMessage));
    return $response;
  }

  public function deletePayment(Request $request, Response $response, string $payment_id): Response
  {
    $payment = $request->getAttribute("payment");
    $paymentData = $this->payment->deletePayment($payment);
    $response->getBody()->write(json_encode($paymentData));
    return $response;
  }
}
