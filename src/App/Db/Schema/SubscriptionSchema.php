<?php

declare(strict_types=1);

namespace App\Db\Schema;

use App\Db\Schema\PackageSchema;
use App\Db\Schema\UserSchema;
use DateTimeImmutable;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
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

  #[OneToOne(targetEntity: UserSchema::class, mappedBy: 'subscribed')]
  public UserSchema|null $customer = null;
  public DateTimeImmutable $createdAt;
  public DateTimeImmutable $updatedAt;
  public DateTimeImmutable $expiresOn;

  public function __construct(UserSchema $user, PackageSchema $package, array $data)
  {
    $this->customer = $user;
    $this->package = $package;
    $this->createdAt = new DateTimeImmutable('now');
    $this->updatedAt = new DateTimeImmutable('now');
    $this->expiresOn = new DateTimeImmutable($data['expires_on']);

    $user->setSubscription($this);
  }

  public function jsonSerialize(): array
  {
    return [
      'id' => $this->id,
      'customer_id' => $this->customer->getId(),
      'package_id' => $this->package->getId(),
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

  public function getCreatedAt(): DateTimeImmutable
  {
    return $this->createdAt;
  }

  public function getUpdatedAt(): DateTimeImmutable
  {
    return $this->updatedAt;
  }

  public function setPackage(PackageSchema $package): void
  {
    $this->package = $package;
  }

  public function setExpiresOn(DateTimeImmutable $expiresOn): void
  {
    $this->expiresOn = $expiresOn;
  }
}
