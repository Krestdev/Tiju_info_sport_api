<?php

declare(strict_types=1);

namespace App\Db\Schema;

use App\Db\Schema\UserSchema;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;
use JsonSerializable;

#[Entity, Table(name: "package")]
class PackageSchema implements JsonSerializable
{
  #[Id, Column(type: 'integer'), GeneratedValue(strategy: 'AUTO')]
  private int $id;

  #[ManyToOne(targetEntity: UserSchema::class, inversedBy: 'packages')]
  private UserSchema|null $author = null;

  #[Column(type: 'string', length: 255)]
  private string $title;
  #[Column(type: 'integer', length: 255)]
  private int $price;

  #[OneToMany(targetEntity: SubscriptionSchema::class, mappedBy: 'package')]
  private Collection $subscriptions;

  #[Column(name: "expires_on", type: 'datetimetz_immutable', nullable: false)]
  private DateTimeImmutable $expiresOn;
  #[Column(name: "created_at", type: 'datetimetz_immutable', nullable: false)]
  private DateTimeImmutable $createdAt;

  #[Column(name: 'updated_at', type: 'datetimetz_immutable', nullable: false)]
  private DateTimeImmutable $updatedAt;

  private function __construct(UserSchema $user, array $data)
  {
    $this->author = $user;
    $this->title = $data['title'];
    $this->price = $data['price'];
    $this->subscriptions = new ArrayCollection();
    $this->createdAt = new DateTimeImmutable();
    $this->updatedAt = new DateTimeImmutable();
    $this->expiresOn = new DateTimeImmutable();

    $user->addPackage($this);
  }

  public function jsonSerialize(): array
  {
    return [
      'id' => $this->id,
      'author' => $this->author,
      'title' => $this->title,
      'price' => $this->price,
      'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
      'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
      'expires_on' => $this->expiresOn->format('Y-m-d H:i:s')
    ];
  }

  public function getId(): int
  {
    return $this->id;
  }

  public function getAuthor(): UserSchema
  {
    return $this->author;
  }

  public function getTitle(): string
  {
    return $this->title;
  }

  public function getPrice(): int
  {
    return $this->price;
  }

  public function getCreatedAt(): DateTimeImmutable
  {
    return $this->createdAt;
  }

  public function getUpdatedAt(): DateTimeImmutable
  {
    return $this->updatedAt;
  }

  public function getExpiresOn(): DateTimeImmutable
  {
    return $this->expiresOn;
  }

  public function setTitle(string $title): void
  {
    $this->title = $title;
  }

  public function setPrice(int $price): void
  {
    $this->price = $price;
  }

  public function addSubscription(SubscriptionSchema $subscription): void
  {
    $this->subscriptions->add($subscription);
  }

  public function setUpdatedAt(DateTimeImmutable $updatedAt): void
  {
    $this->updatedAt = $updatedAt;
  }

  public function setExpiresOn(DateTimeImmutable $expiresOn): void
  {
    $this->expiresOn = $expiresOn;
  }
}
