<?php

declare(strict_types=1);

namespace App\Db\Schema;

use App\Db\Schema\SubscriptionSchema;
use App\Db\Schema\UserSchema;
use DateTimeImmutable;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use JsonSerializable;

#[Entity, Table(name: "payments")]
class PaymentSchema implements JsonSerializable
{
  #[Id, Column(type: 'integer'), GeneratedValue(strategy: 'AUTO')]
  private int $id;

  #[Column(type: 'string', length: 255)]
  private string $payout_id; // {{generatedUUID}},

  #[Column(type: 'integer')]
  private int $amount; // 15,

  #[Column(type: 'string', length: 3)]
  private string $currency; // ZMW,

  #[Column(type: 'string', length: 255)]
  private string $correspondent; // MTN_MOMO_ZMB,

  // [
  //   "address" => [
  //     "value" => 260763456789 // 260763456789
  //   ],
  //   "type" => "MSDIN" // MSISDN
  // ];

  #[Column(type: 'integer', length: 12)]
  private int $address;
  private string $type = "MSISDN";

  #[Column(name: "customerTimestamp", type: 'datetimetz_immutable', nullable: false)]
  private DateTimeImmutable $customerTimestamp; // 2020-02-21T17:32:28Z,

  #[Column(type: 'string', length: 22)]
  private string $statementDescription; // Note of 4 to 22 chars,

  #[Column(type: 'string', length: 3)]
  private string $country; // ZMB,

  #[Column(type: 'string', length: 255)]
  private ?string $metadata = null;

  // [
  //   [
  //     "fieldName" => "subscriptionId", // orderId,
  //     "fieldValue" => 2 // 
  //   ],
  //   [
  //     "fieldName" => "customerId", // customerId,
  //     "fieldValue" => 59, // customer@email.com,
  //   ]
  // ];

  #[ManyToOne(targetEntity: UserSchema::class, inversedBy: "payments")]
  private UserSchema $customer;

  #[ManyToOne(targetEntity: SubscriptionSchema::class, inversedBy: "payments")]
  private SubscriptionSchema $subscription;

  #[Column(type: 'string', length: 255)]
  private string $status;

  public function __construct(UserSchema $user, SubscriptionSchema $subscription, array $data)
  {
    $this->payout_id = $data["payout_id"];
    $this->amount = $subscription->getPackage()->getPrice();
    $this->currency = $data["currency"];
    $this->correspondent = $data["correspondent"];

    $this->address = (int)$data["address"];

    $this->customerTimestamp = new DateTimeImmutable('now');
    $this->statementDescription = $data["statementDescription"];
    $this->country = $data["country"] ?? "CMR";

    $this->metadata = $data["metadata"];

    $this->status = "CREATING";

    $this->customer = $user;
    $this->subscription = $subscription;

    $user->addPayment($this);
    $subscription->addPayment($this);
  }

  public function jsonSerialize(): array
  {
    return [
      "id" => $this->id,
      "status" => $this->status,
      "payoutId" => $this->payout_id,
      "amount" => $this->amount,
      "currency" => $this->currency,
      "country" => $this->country,
      "correspondent" => $this->correspondent,
      "recipient" => [
        "type" => $this->type,
        "address" => [
          "value" => $this->address,
        ],
      ],
      "crated_at" => $this->customerTimestamp->format("Y-m-d H:i:s"),
      "description" => $this->statementDescription,
      "metaData" => $this->metadata
    ];
  }

  public function getId(): int
  {
    return $this->id;
  }
  public function getPayoutId(): string
  {
    return $this->payout_id;
  }
  public function getAmount(): int
  {
    return $this->amount;
  }
  public function getCurrency(): string
  {
    return $this->currency;
  }
  public function getCorrespondent(): string
  {
    return $this->correspondent;
  }

  public function getAddress(): int
  {
    return $this->address;
  }
  public function getType(): string
  {
    return $this->type;
  }
  public function getCustomerTimestamp(): DateTimeImmutable
  {
    return $this->customerTimestamp;
  }

  public function getSubscription(): SubscriptionSchema
  {
    return $this->subscription;
  }

  public function getCustomer(): UserSchema
  {
    return $this->customer;
  }

  public function getStatementDescription(): string
  {
    return $this->statementDescription;
  }

  public function getCountry(): string
  {
    return $this->country;
  }

  public function getStatus(): string
  {
    return $this->status;
  }

  public function setStatus(string $status): void
  {
    $this->status = $status;
  }
}
