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

  #[OneToMany(targetEntity: SubscriptionSchema::class, mappedBy: 'package', cascade: ['persist', 'remove'], orphanRemoval: true)]
  private Collection $subscriptions;

  #[Column(name: "period", type: 'integer', nullable: false)]
  private int $period;
  #[Column(name: "created_at", type: 'datetimetz_immutable', nullable: false)]
  private DateTimeImmutable $createdAt;

  #[Column(name: 'updated_at', type: 'datetimetz_immutable', nullable: false)]
  private DateTimeImmutable $updatedAt;

  public function __construct(UserSchema $user, array $data)
  {
    $this->author = $user;
    $this->title = $data['title'];
    $this->price = $data['price'];
    $this->subscriptions = new ArrayCollection();
    $this->createdAt = new DateTimeImmutable();
    $this->updatedAt = new DateTimeImmutable();
    $this->period = $data['period'];

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
      'period' => $this->period
    ];
  }

  public function jsonSerializeDeleted(): array
  {
    return [
      'id' => $this->id,
      'author' => $this->author,
      'title' => $this->title,
      'price' => $this->price,
      'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
      'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
      'period' => $this->period
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

  public function getPeriod(): int
  {
    return $this->period;
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

  public function setPeriod(int $period): void
  {
    $this->period = $period;
  }
}
