<?php

declare(strict_types=1);

namespace App\Db\Schema;

use DateTimeImmutable;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;
use JsonSerializable;

#[Entity, Table(name: "advertisements")]
class AdsSchema implements JsonSerializable
{
  #[Id, Column(type: 'integer'), GeneratedValue(strategy: 'AUTO')]
  private int $id;

  #[ManyToOne(targetEntity: UserSchema::class, inversedBy: 'advertisements')]
  private UserSchema|null $author = null;

  #[Column(type: 'string', length: 255)]
  private string $title;

  #[Column(type: 'string', length: 255)]
  private string $description;

  #[Column(type: 'string', length: 255)]
  private string $url;

  #[OneToOne(targetEntity: ImageSchema::class, inversedBy: 'advertisment', cascade: ['persist', 'remove'], orphanRemoval: true)]
  private ?ImageSchema $image = null;

  #[Column(name: "created_at", type: 'datetimetz_immutable', nullable: false)]
  private DateTimeImmutable $createdAt;

  #[Column(name: 'updated_at', type: 'datetimetz_immutable', nullable: false)]
  private DateTimeImmutable $updatedAt;

  public function __construct(UserSchema $user, array $data)
  {
    $this->author = $user;
    $this->title = $data['title'];
    $this->description = $data['description'];
    $this->url = $data['url'];
    $this->createdAt = new DateTimeImmutable();
    $this->updatedAt = new DateTimeImmutable();

    $user->addAdvertisement($this);
  }

  public function jsonSerialize(): array
  {
    return [
      'id' => $this->id,
      'author' => $this->author,
      'title' => $this->title,
      'description' => $this->description,
      'url' => $this->url,
      'image' => $this->image,
      'createdAt' => $this->createdAt->format('Y-m-d H:i:s'),
      'updatedAt' => $this->updatedAt->format('Y-m-d H:i:s')
    ];
  }

  public function jsonSerializeDeleted(): array
  {
    return [
      'id' => $this->id,
      'author' => $this->author,
      'title' => $this->title,
      'description' => $this->description,
      'url' => $this->url,
      'image' => $this->image,
      'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
      'updated_at' => $this->updatedAt->format('Y-m-d H:i:s')
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

  public function getImage(): ImageSchema
  {
    return $this->image;
  }

  public function getDescription(): string
  {
    return $this->description;
  }

  public function getUrl(): string
  {
    return $this->url;
  }

  public function getCreatedAt(): DateTimeImmutable
  {
    return $this->createdAt;
  }

  public function getUpdatedAt(): DateTimeImmutable
  {
    return $this->updatedAt;
  }

  public function setTitle(string $title): void
  {
    $this->title = $title;
  }

  public function setDescription(string $description): void
  {
    $this->description = $description;
  }

  public function setUrl(string $url): void
  {
    $this->url = $url;
  }

  public function setImage(?ImageSchema $image): void
  {
    $this->image = $image;
  }
}
