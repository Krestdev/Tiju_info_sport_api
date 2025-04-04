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
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;
use Google\Service\Books\Category;
use InvalidArgumentException;
use JsonSerializable;

#[Entity, Table(name: "category")]
class CategorySchema implements JsonSerializable
{
  #[Id, Column(type: 'integer'), GeneratedValue(strategy: 'AUTO')]
  private int $id;

  #[ManyToOne(targetEntity: UserSchema::class, inversedBy: 'categories')]
  private UserSchema|null $author = null;

  // Many comments reponds to one comment
  #[ManyToOne(targetEntity: CategorySchema::class, inversedBy: 'children')]
  #[JoinColumn(name: 'parent_id', referencedColumnName: 'id')]
  private CategorySchema|null $parent = null;

  #[OneToMany(targetEntity: CategorySchema::class, mappedBy: 'parent', cascade: ['persist', 'remove'])]
  private Collection $children;

  #[Column(type: 'string', length: 255)]
  private string $title;

  #[Column(type: 'string', length: 255)]
  private string $description;

  #[Column(type: 'string', length: 255)]
  private string $image;

  #[OneToMany(targetEntity: ArticleSchema::class, mappedBy: 'category', cascade: ['persist', 'remove'], orphanRemoval: true)]
  private Collection $articles;

  #[Column(name: "created_at", type: 'datetimetz_immutable', nullable: false)]
  private DateTimeImmutable $createdAt;

  #[Column(name: 'updated_at', type: 'datetimetz_immutable', nullable: false)]
  private DateTimeImmutable $updatedAt;

  public function __construct(UserSchema $user, array $data)
  {
    $this->title = $data['title'];
    $this->author = $user;
    $this->description = $data['description'];
    $this->image = $data['image'];
    $this->articles = new ArrayCollection();
    $this->createdAt = new DateTimeImmutable();
    $this->updatedAt = new DateTimeImmutable();

    $user->addCategories($this);
  }

  public function jsonSerialize(): array
  {
    return [
      'id' => $this->id,
      'title' => $this->title,
      'author' => $this->author,
      'description' => $this->description,
      'parent' => $this?->parent?->getId(),
      'image' => $this->image,
      'articles' => $this->articles->toArray(),
      'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
      'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
    ];
  }

  public function jsonSerializeDeleted(): array
  {
    return [
      'id' => $this->id,
      'title' => $this->title,
      'author' => $this->author,
      'description' => $this->description,
      'image' => $this->image,
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

  public function getParent(): CategorySchema
  {
    if (! isset($this->parent)) {
      throw new InvalidArgumentException("No parent found");
    }
    return $this->parent;
  }

  public function getChildren(): Collection
  {
    return $this->children;
  }

  public function getAuthor(): UserSchema
  {
    return $this->author;
  }

  public function getDescription(): string
  {
    return $this->description;
  }

  public function getImage(): string
  {
    return $this->image;
  }

  public function getArticles(): Collection
  {
    return $this->articles;
  }

  public function setTitle(string $title): void
  {
    $this->title = $title;
  }

  public function setParent(CategorySchema $parent): void
  {
    $this->parent = $parent;
    $parent->getChildren()->add($this);
  }

  public function setDescription(string $description): void
  {
    $this->description = $description;
  }

  public function setImage(string $image): void
  {
    $this->image = $image;
  }

  public function addArticle(ArticleSchema $article): void
  {
    if (!$this->articles->contains($article)) {
      $this->articles->add($article);
      $article->setCategory($this);
    }
  }
};
