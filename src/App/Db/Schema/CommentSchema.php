<?php

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
use InvalidArgumentException;
use JsonSerializable;

#[Entity, Table(name: "comments")]
class CommentSchema implements JsonSerializable
{
  #[Id, Column(type: 'integer'), GeneratedValue(strategy: 'AUTO')]
  private int $id;

  //  Many comments have one owner user
  #[ManyToOne(targetEntity: UserSchema::class, inversedBy: 'comments')]
  #[JoinColumn(name: 'user_id', referencedColumnName: 'id')]
  private UserSchema|null $author = null;

  #[Column(type: 'string', length: 255)]
  private string $message;

  /**
   * One Message has many response
   * @var Collection<int, CommentSchema>
   */
  #[OneToMany(targetEntity: CommentSchema::class, mappedBy: 'parent', cascade: ['persist', 'remove'])]
  private Collection $response;

  // Many comments reponds to one comment
  #[ManyToOne(targetEntity: CommentSchema::class, inversedBy: 'response')]
  #[JoinColumn(name: 'parent_id', referencedColumnName: 'id')]
  private CommentSchema|null $parent = null;

  /**
   * Many Comments Has Many user likes
   * @var Collection<int, UserSchema>
   */
  #[ManyToMany(targetEntity: UserSchema::class, inversedBy: 'liked', cascade: ['persist', 'remove'])]
  #[JoinTable(name: 'users_liks')]
  private Collection $likes;

  // Many Comments Has Many signals
  #[ManyToMany(targetEntity: UserSchema::class, inversedBy: 'signaled', cascade: ['persist', 'remove'])]
  #[JoinTable(name: 'users_signals')]
  private Collection $signals;

  // Many comment belongs to an article
  #[ManyToOne(targetEntity: ArticleSchema::class, inversedBy: 'comments')]
  #[JoinColumn(name: 'article_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
  private ArticleSchema|null $article = null;

  #[Column(name: "created_at", type: 'datetimetz_immutable', nullable: false)]
  private DateTimeImmutable $createdAt;

  #[Column(name: "updated_at", type: 'datetimetz_immutable', nullable: false)]
  private DateTimeImmutable $updatedAt;

  public function __construct(UserSchema $author, ArticleSchema $article, array $data)
  {
    $this->author = $author;
    $this->article = $article;
    $this->message = $data['message'];
    $this->likes = new ArrayCollection();
    $this->response = new ArrayCollection();
    $this->signals = new ArrayCollection();
    $this->createdAt = new DateTimeImmutable('now');
    $this->updatedAt = new DateTimeImmutable('now');

    $author->getComments()->add($this);
    $article->getComments()->add($this);
  }

  public function jsonSerialize(): array
  {
    return [
      'id' => $this->id,
      'author' => $this->author,
      'article_id' => $this->article->getId(),
      'parent' => $this->parent?->getId(),
      'message' => $this->message,
      'likes' => $this->likes->map(fn(UserSchema $user) => $user->getId())->toArray(),
      'response' => $this->response->toArray(),
      'signals' => $this->signals->map(fn(UserSchema $user) => $user->getId())->toArray(),
      'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
      'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
    ];
  }
  public function jsonSerializeDelete(): array
  {
    return [
      'id' => $this->id,
      'author' => $this->author,
      'article_id' => $this->article->getId(),
      'message' => $this->message,
      'likes' => $this->likes->map(fn(UserSchema $user) => $user->getId())->toArray(),
      'response' => $this->response->toArray(),
      'signals' => $this->signals->map(fn(UserSchema $user) => $user->getId())->toArray(),
      'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
      'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
    ];
  }

  public function getId(): int
  {
    return $this->id;
  }

  public function getMessage(): string
  {
    return $this->message;
  }

  public function getResponse(): Collection
  {
    return $this->response;
  }

  public function getParent(): CommentSchema
  {
    if (! isset($this->parent)) {
      throw new InvalidArgumentException("No parent found");
    }
    return $this->parent;
  }

  public function getLiks(): Collection
  {
    return $this->likes;
  }

  public function getSignals(): Collection
  {
    return $this->signals;
  }
  public function getArticle(): ArticleSchema
  {
    return $this->article;
  }

  public function getUpdatedAt(): DateTimeImmutable
  {
    return $this->updatedAt;
  }

  public function getCreatedAt(): DateTimeImmutable
  {
    return $this->createdAt;
  }

  public function getAuthor(): ?UserSchema
  {
    return $this->author;
  }

  public function setParent(CommentSchema $parent): void
  {
    $this->parent = $parent;
    $parent->getResponse()->add($this);
  }

  // handle response
  public function addRespond(CommentSchema $response): void
  {
    $this->response->add($response);
    $response->setParent($this);
  }

  // handle likes
  public function addLikes(UserSchema $user): void
  {
    if (!$this->likes->contains($user)) {
      $this->likes->add($user);
      $user->getLiked()->add($this);
    }
  }

  public function removeLikes(UserSchema $user): void
  {
    if ($this->likes->contains($user)) {
      $this->likes->removeElement($user);
      $user->getLiked()->removeElement($this);
    }
  }

  // handle signals
  public function addSignales(UserSchema $user): void
  {
    if (!$this->signals->contains($user)) {
      $this->signals->add($user);
      $user->getSignaled()->add($this);
    }
  }

  public function removeSignales(UserSchema $user): void
  {
    if ($this->signals->contains($user)) {
      $this->signals->removeElement($user);
      $user->getSignaled()->removeElement($this);
    }
  }

  // handle articles

  public function setMessage(string $message): void
  {
    $this->message = $message;
  }
  public function setUpdatedAt(DateTimeImmutable $updatedAt): void
  {
    $this->updatedAt = $updatedAt;
  }
}
