<?php

declare(strict_types=1);

namespace App\Db\Repository;

use App\Db\Schema\PaymentSchema;
use App\Db\Schema\SubscriptionSchema;
use App\Db\Schema\UserSchema;
use DateInterval;
use DateTimeImmutable;
use Doctrine\ORM\EntityManager;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface as Response;

class PaymentService
{
  private EntityManager $em;

  public function __construct(EntityManager $em)
  {
    $this->em = $em;
  }


  public function getPaymentById(int $id): PaymentSchema
  {
    return $this->em->getRepository(PaymentSchema::class)->findOneBy(["id" => $id]);
  }

  public function getPaymentByPayoutId(string $payout_id): PaymentSchema
  {
    return $this->em->getRepository(PaymentSchema::class)->findOneBy(["payout_id" => $payout_id]);
  }

  public function updatePayment(int $id, array $data): PaymentSchema
  {
    $payment = $this->em->getRepository(PaymentSchema::class)->findOneBy(["id" => $id]);
    $payment->setStatus($data["status"]);
    $this->em->persist($payment);
    $this->em->flush();
    return $payment;
  }

  public function makePayment(UserSchema $user, SubscriptionSchema $subscription, array $data): PaymentSchema
  {
    $data["currency"] = "XAF";
    // var_dump($data);
    $payment = new PaymentSchema($user, $subscription, $data);

    $this->em->persist($payment);
    $this->em->flush();

    $this->sendPaymentRequest($payment);

    return $payment;
  }

  public function sendPaymentRequest(PaymentSchema $payment): Response
  {
    $data = json_encode([
      "payoutId" => $payment->getPayoutId(),
      "amount" => $payment->getSubscription()->getPackage()->getPrice(),
      "currency" => "XAF",
      "country" => "CMR",
      "correspondent" => $payment->getCorrespondent(), // "MTN_MOMO_CMR" / "ORANGE_CMR"
      "recipient" => [
        "type" => "MSISDN",
        "address" => [
          "value" => "237" . $payment->getAddress()
        ]
      ],
      "customerTimestamp" => $payment->getCustomerTimestamp()->format('Y-m-d\TH:i:s\Z'), // "2020-02-21T17:32:28Z",
      "statementDescription" => $payment->getStatementDescription(),
      "metadata" => [
        [
          "fieldName" => "subscriptionId",
          "fieldValue" => "Tiju-" . $payment->getSubscription()->getId(),
        ],
        [
          "fieldName" => "customerId",
          "fieldValue" => $payment->getCustomer()->getUsername()
        ]
      ]
    ]);

    try {
      $client = new Client();

      $response = $client->post('https://api.sandbox.pawapay.io/payouts', [
        'headers' => [
          'Authorization' => 'Bearer ' . $_ENV['PAYMENT_KEY'],
          'Content-Type' => 'application/json'
        ],
        'body' => $data,
        'allow_redirects' => true,
        'connect_timeout' => 30,
        'http_errors' => false // Disable throwing exceptions for non-2xx responses
      ]);

      $http_code = $response->getStatusCode();
      $body = (string) $response->getBody();
      $decodedBody = json_decode($body, true);

      if ($http_code != 200) {
        file_put_contents(__DIR__ . '/log/payment_error.log', "Error_code = " . $decodedBody["errorCode"] . "Http_Code" . $http_code . " Error_message = " . $decodedBody["errorMessage"] . "\n", FILE_APPEND);
      } else {
        if ($decodedBody["status"] !== "DUPLICATE_IGNORED") {
          $payment->setStatus($decodedBody["status"]);
          $payment->getSubscription()->setStatus($decodedBody["status"]);
          $this->em->persist($payment);
          $this->em->flush();
        }
      }
      return $response;
    } catch (RequestException $e) {
      file_put_contents(__DIR__ . '/log/request_error.log', 'Request error: ' . $e->getMessage(), FILE_APPEND);
      throw $e;
    };
  }

  public function checkPaymentStatus(PaymentSchema $payment): Response
  {
    try {
      $client = new Client();

      $response = $client->get("https://api.sandbox.pawapay.io/payouts/" . $payment->getPayoutId(), [
        'headers' => [
          'Authorization' => 'Bearer ' . $_ENV['PAYMENT_KEY'],
          'Content-Type' => 'application/json'
        ],
        'allow_redirects' => true,
        'connect_timeout' => 30,
        'http_errors' => false // Disable throwing exceptions for non-2xx responses
      ]);

      $http_code = $response->getStatusCode();
      $body = (string) $response->getBody();
      $decodedBody = json_decode($body, true);

      if ($http_code != 200) {
        file_put_contents(__DIR__ . '/log/payment_error.log', "Error_code = " . $decodedBody["errorCode"] . "Http_Code" . $http_code . " Error_message = " . $decodedBody["errorMessage"] . "\n", FILE_APPEND);
      } else {
        $payment->setStatus($decodedBody[0]["status"]);
        $payment->getSubscription()->setStatus($decodedBody[0]["status"]);
        if ($decodedBody[0]["status"] === "COMPLETED") {
          $subscription = $payment->getSubscription();
          $baseDate = new DateTimeImmutable();
          $newDate = $baseDate->add(new DateInterval('P' . $subscription->getPackage()->getPeriod() . 'D'));
          $subscription->setExpiresOn($newDate);
        }
        $this->em->persist($payment);
        $this->em->flush();
      }

      return $response;
    } catch (RequestException $e) {
      file_put_contents(__DIR__ . '/log/request_check_error.log', 'Request error: ' . $e->getMessage(), FILE_APPEND);
      throw $e;
    };
  }

  public function deletePayment(PaymentSchema $payment)
  {
    $payment->getCustomer()->removePayment($payment);
    $payment->getSubscription()->removePayment($payment);

    $paymentData = $payment->jsonSerialize();
    $this->em->remove($payment);
    $this->em->flush();

    return $paymentData;
  }
}
