<?php

declare(strict_types=1);

namespace App\Db\Schema;

use App\Db\Schema\PackageSchema;
use App\Db\Schema\UserSchema;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;
use JsonSerializable;

#[Entity, Table(name: 'subscriptions')]
class SubscriptionSchema implements JsonSerializable
{
  #[Id, Column(type: 'integer'), GeneratedValue(strategy: 'AUTO')]
  public int $id;

  #[ManyToOne(targetEntity: PackageSchema::class, inversedBy: 'subscriptions')]
  public PackageSchema|null $package = null;

  #[ManyToOne(targetEntity: UserSchema::class, inversedBy: 'subscribed')]
  public UserSchema|null $customer = null;

  #[OneToMany(targetEntity: PaymentSchema::class, mappedBy: "subscription", cascade: ["persist"])]
  private Collection $payments;

  #[Column(type: 'string', length: 20)]
  private string $status;

  #[Column(name: "created_at", type: 'datetimetz_immutable', nullable: false)]
  private DateTimeImmutable $createdAt;

  #[Column(name: 'updated_at', type: 'datetimetz_immutable', nullable: false)]
  private DateTimeImmutable $updatedAt;

  #[Column(name: 'expires_on', type: 'datetimetz_immutable', nullable: false)]
  public DateTimeImmutable $expiresOn;

  public function __construct(UserSchema $user, PackageSchema $package, array $data)
  {
    $this->customer = $user;
    $this->package = $package;
    $this->payments = new ArrayCollection();
    $this->status = $data['status'] ?? 'UNPAID';
    $this->createdAt = new DateTimeImmutable('now');
    $this->updatedAt = new DateTimeImmutable('now');
    $this->expiresOn = $data['expires_on'];

    $user->addSubscription($this);
  }

  public function jsonSerialize(): array
  {
    return [
      'id' => $this->id,
      'customer_id' => $this->customer->getId(),
      'package_id' => $this->package->getId(),
      'status' => $this->status,
      'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
      'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
      'expires_on' => $this->expiresOn->format('Y-m-d H:i:s')
    ];
  }

  public function jsonSerializeDeleted(): array
  {
    return [
      'id' => $this->id,
      'customer_id' => $this->customer->getId(),
      'package_id' => $this->package->getId(),
      'status' => $this->status,
      'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
      'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
      'expires_on' => $this->expiresOn->format('Y-m-d H:i:s')
    ];
  }

  public function getId(): int
  {
    return $this->id;
  }

  public function getCustomer(): UserSchema
  {
    return $this->customer;
  }

  public function getPackage(): PackageSchema
  {
    return $this->package;
  }

  public function getPayments(): Collection
  {
    return $this->payments;
  }

  public function getExpiresOn(): DateTimeImmutable
  {
    return $this->expiresOn;
  }

  public function getCreatedAt(): DateTimeImmutable
  {
    return $this->createdAt;
  }

  public function getUpdatedAt(): DateTimeImmutable
  {
    return $this->updatedAt;
  }

  public function setCustomer(?UserSchema $customer): void
  {
    $this->customer = $customer;
  }

  public function setPackage(?PackageSchema $package): void
  {
    $this->package = $package;
  }

  public function getStatus(): string
  {
    return $this->status;
  }

  public function setStatus(string $status): void
  {
    if ($this->status !== "COMPLETED") {
      $this->status = $status;
    }
  }

  public function setExpiresOn(DateTimeImmutable $expiresOn): void
  {
    $this->expiresOn = $expiresOn;
  }

  public function addPayment(PaymentSchema $payment): void
  {
    $this->payments->add($payment);
  }

  public function removePayment(PaymentSchema $payment): void
  {
    if ($this->payments->contains($payment)) {
      $this->payments->removeElement($payment);
    }
  }
}
