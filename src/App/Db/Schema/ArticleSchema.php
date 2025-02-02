<?php

declare(strict_types=1);

namespace App\Db\Schema;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\PersistentCollection;
use JsonSerializable;

#[Entity, Table(name: "articles")]
class ArticleSchema implements JsonSerializable
{
  #[Id, Column(type: 'integer'), GeneratedValue(strategy: 'AUTO')]
  private int $id;

  #[Column(type: 'string', length: 255)]
  private string $type;

  #[Column(type: 'string', length: 255)]
  private string $title;

  #[Column(type: 'string', length: 100)]
  private string $summary;

  #[Column(type: 'string', length: 255)]
  private string $description;
  // private string $media: string[],

  #[OneToMany(targetEntity: CommentSchema::class, mappedBy: 'article', cascade: ['persist', 'remove'], orphanRemoval: true)]
  private Collection $comments;

  #[ManyToMany(targetEntity: UserSchema::class, inversedBy: 'likeBlogs')]
  #[JoinTable(name: 'articleLike_blogs')]
  private Collection $likes;

  #[ManyToOne(targetEntity: UserSchema::class, inversedBy: 'articles')]
  private UserSchema|null $author = null;

  #[ManyToOne(targetEntity: CategorySchema::class, inversedBy: 'articles')]
  #[JoinColumn(name: 'category_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
  private CategorySchema|null $category = null;

  #[Column(name: "created_at", type: 'datetimetz_immutable', nullable: false)]
  private DateTimeImmutable $createdAt;

  #[Column(name: 'updated_at', type: 'datetimetz_immutable', nullable: false)]
  private DateTimeImmutable $updatedAt;
  // private string $abonArticle: Abonnement

  public function __construct(UserSchema $user, CategorySchema $category, array $data)
  {
    $this->author = $user;
    $this->title = $data['title'];
    $this->type = $data['type'];
    $this->summary = $data['summary'];
    $this->description = $data['description'];
    $this->category = $category;
    $this->createdAt = new DateTimeImmutable('now');
    $this->updatedAt = new DateTimeImmutable('now');
    $this->comments = new ArrayCollection();
    $this->likes = new ArrayCollection();

    $user->addArticles($this);
    $category->addArticle($this);
  }

  public function jsonSerialize(): array
  {
    return [
      'id' => $this->id,
      'type' => $this->type,
      'title' => $this->title,
      'summery' => $this->summary,
      'description' => $this->description,
      'author' => $this->author,
      'comments' => $this->comments->toArray(),
      'likes' => $this->likes->count(),
      'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
      'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
    ];
  }

  public function jsonSerializeDeleted(): array
  {
    return [
      'id' => $this->id,
      'type' => $this->type,
      'title' => $this->title,
      'summery' => $this->summary,
      'description' => $this->description,
      'author' => $this->author,
      'likes' => $this->likes->count(),
      'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
      'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
    ];
  }

  public function getId(): int
  {
    return $this->id;
  }

  public function getTitle(): string
  {
    return $this->title;
  }

  public function getType(): string
  {
    return $this->type;
  }

  public function getsummary(): string
  {
    return $this->summary;
  }

  public function getDescription(): string
  {
    return $this->description;
  }

  public function getAuthor(): UserSchema
  {
    return $this->author;
  }

  public function getCreatedAt(): DateTimeImmutable
  {
    return $this->createdAt;
  }

  public function getUpdatedAt(): DateTimeImmutable
  {
    return $this->updatedAt;
  }

  public function getComments(): Collection
  {
    return $this->comments;
  }

  public function getLikes(): Collection
  {
    return $this->likes;
  }

  public function setTitle(string $title): void
  {
    $this->title = $title;
  }

  public function setType(string $type): void
  {
    $this->type = $type;
  }

  public function setsummary(string $summary): void
  {
    $this->summary = $summary;
  }

  public function setDescription(string $description): void
  {
    $this->description = $description;
  }

  // handles likes
  public function addLikes(UserSchema $user): void
  {
    if (!$this->likes->contains($user)) {
      $this->likes->add($user);
      $user->getLikedBlogs()->add($this);
    }
  }

  public function removeLikes(UserSchema $user): void
  {
    if ($this->likes->contains($user)) {
      $this->likes->removeElement($user);
      $user->getLikedBlogs()->removeElement($this);
    }
  }

  public function setCategory(CategorySchema $category): void
  {
    $this->category = $category;
  }
};
