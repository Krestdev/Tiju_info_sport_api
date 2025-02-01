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
  #[OneToMany(targetEntity: CommentSchema::class, mappedBy: 'parent')]
  private Collection $response;

  // Many comments reponds to one comment
  #[ManyToOne(targetEntity: CommentSchema::class, inversedBy: 'response')]
  #[JoinColumn(name: 'parent_id', referencedColumnName: 'id')]
  private CommentSchema|null $parent = null;

  /**
   * Many Comments Has Many user likes
   * @var Collection<int, UserSchema>
   */
  #[ManyToMany(targetEntity: UserSchema::class, inversedBy: 'liked')]
  #[JoinTable(name: 'users_liks')]
  private Collection $likes;

  // Many Comments Has Many signals
  #[ManyToMany(targetEntity: UserSchema::class, inversedBy: 'signaled')]
  #[JoinTable(name: 'users_signals')]
  private Collection $signals;

  #[Column(name: "created_at", type: 'datetimetz_immutable', nullable: false)]
  private DateTimeImmutable $createdAt;

  #[Column(name: "updated_at", type: 'datetimetz_immutable', nullable: false)]
  private DateTimeImmutable $updatedAt;

  public function __construct(array $data)
  {
    $this->message = $data['message'];
    $this->likes = new ArrayCollection();
    $this->response = new ArrayCollection();
    $this->signals = new ArrayCollection();
    $this->createdAt = new DateTimeImmutable('now');
    $this->updatedAt = new DateTimeImmutable('now');
  }

  public function jsonSerialize(): array
  {
    return [
      'message' => $this->message,
      'likes' => $this->likes,
      'response' => $this->response,
      'signals' => $this->signals,
      'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
      'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
    ];
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
      throw new InvalidArgumentException("No parent foind");
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

  public function setAuthor(UserSchema $author): void
  {
    $this->author = $author;
  }

  public function setMessage(string $message): void
  {
    $this->message = $message;
  }
  public function setUpdatedAt(DateTimeImmutable $updatedAt): void
  {
    $this->updatedAt = $updatedAt;
  }
}
