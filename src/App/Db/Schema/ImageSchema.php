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
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;
use JsonSerializable;

#[Entity, Table(name: "images")]
class ImageSchema implements JsonSerializable
{
  #[Id, Column(type: 'integer'), GeneratedValue(strategy: 'AUTO')]
  private int $id;

  #[Column(type: 'string', length: 255, nullable: true)]
  private ?string $location;

  #[Column(type: 'integer', length: 255)]
  private int $size;

  #[ManyToOne(targetEntity: ArticleSchema::class, inversedBy: 'images')]
  #[JoinColumn(name: 'article_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
  private ?ArticleSchema $article;

  #[OneToOne(targetEntity: AdsSchema::class, mappedBy: 'image', cascade: ['persist', 'remove'])]
  private ?AdsSchema $advertisment;

  #[OneToOne(targetEntity: UserSchema::class, mappedBy: 'profile', cascade: ['persist', 'remove'])]
  private ?UserSchema $user;

  #[Column(name: "created_at", type: 'datetimetz_immutable', nullable: false)]
  private DateTimeImmutable $createdAt;

  #[Column(name: 'updated_at', type: 'datetimetz_immutable', nullable: false)]
  private DateTimeImmutable $updatedAt;

  public function __construct(?UserSchema $user, ?AdsSchema $ads, ?ArticleSchema $article, array $data)
  {
    $this->size = $data['size'];
    $this->location = $data['location'] ?? null;
    $this->article = $article;
    $this->advertisment = $ads;
    $this->user = $user;
    $this->createdAt = new DateTimeImmutable();
    $this->updatedAt = new DateTimeImmutable();

    if ($user) {
      $user->setProfile($this);
    }
    if ($ads) {
      $ads->setImage($this);
    }
    if ($article) {
      $article->addImage($this);
    }
  }

  public function jsonSerialize(): array
  {
    return [
      'id' => $this->id,
      // 'author' => $this->user,
      // 'ads' => $this->advertisment,
      // 'article' => $this->article,
      'location' => $this->location,
      'size' => $this->size,
      'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
      'updated_at' => $this->updatedAt->format('Y-m-d H:i:s')
    ];
  }

  public function jsonSerializeDeleted(): array
  {
    return [
      'id' => $this->id,
      // 'author' => $this->user,
      // 'ads' => $this->advertisment,
      // 'article' => $this->article,
      'size' => $this->size,
      'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
      'updated_at' => $this->updatedAt->format('Y-m-d H:i:s')
    ];
  }

  public function getId(): int
  {
    return $this->id;
  }

  public function getUser(): ?UserSchema
  {
    return $this->user;
  }

  public function getAdvertisment(): ?AdsSchema
  {
    return $this->advertisment;
  }

  public function getArticle(): ?ArticleSchema
  {
    return $this->article;
  }

  public function getLocation(): string
  {
    return $this->location;
  }

  public function getSize(): int
  {
    return $this->size;
  }

  public function getCreatedAt(): DateTimeImmutable
  {
    return $this->createdAt;
  }

  public function getUpdatedAt(): DateTimeImmutable
  {
    return $this->updatedAt;
  }

  public function setSize(int $size): void
  {
    $this->size = $size;
  }

  public function setLocation(string $location): void
  {
    $this->location = $location;
  }

  public function setUser(UserSchema $user): void
  {
    $this->user = $user;
  }

  public function setArticle(ArticleSchema $article): void
  {
    $this->article = $article;
  }

  public function setAds(AdsSchema $ads): void
  {
    $this->advertisment = $ads;
  }

  public function setUpdatedAt(DateTimeImmutable $updatedAt): void
  {
    $this->updatedAt = $updatedAt;
  }
}
