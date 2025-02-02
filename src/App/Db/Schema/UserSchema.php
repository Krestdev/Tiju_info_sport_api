<?php

namespace App\Db\Schema;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\PersistentCollection;
use JsonSerializable;

#[Entity, Table(name: 'users')]
class UserSchema implements JsonSerializable
{
  #[Id, Column(type: 'integer'), GeneratedValue(strategy: 'AUTO')]
  private int $id;

  #[Column(type: 'string', length: 255)]
  private string $name;

  #[Column(name: 'nick_name', type: 'string', length: 255)]
  private string $nickName;

  #[Column(type: 'string', length: 100, unique: true, nullable: false)]
  private string $email;

  #[Column(type: 'string', length: 255)]
  private string $phone;

  #[Column(type: 'string', length: 10)]
  private string $sex;

  #[Column(type: 'string', length: 50)]
  private string $town;

  #[Column(type: 'string', length: 50)]
  private string $country;

  #[Column(type: 'string', length: 255)]
  private string $photo;

  #[Column(type: 'string', length: 100, unique: true, nullable: false)]
  private string $password;

  #[Column(type: 'string', length: 100)]
  private string $role;

  /**
   * A user has many comments
   * @var Collection<int, CommentSchema>
   */
  #[OneToMany(targetEntity: CommentSchema::class, mappedBy: 'author')]
  private Collection $comments;

  /**
   * Many users like many Comments
   * @var Collection<int, CommentSchema>
   */
  #[ManyToMany(targetEntity: CommentSchema::class, mappedBy: 'likes')]
  private Collection $liked;

  /**
   * Many user signal many messages
   * @var Collection<int, CommentSchema>
   */
  #[ManyToMany(targetEntity: CommentSchema::class, mappedBy: 'signals')]
  private Collection $signaled;

  // articles

  #[OneToMany(targetEntity: ArticleSchema::class, mappedBy: 'author')]
  private Collection $articles;

  #[ManyToMany(targetEntity: ArticleSchema::class, mappedBy: 'likes')]
  private Collection $likeBlogs;

  #[Column(name: "created_at", type: 'datetimetz_immutable', nullable: false)]
  private DateTimeImmutable $createdAt;

  #[Column(name: 'updated_at', type: 'datetimetz_immutable', nullable: false)]
  private DateTimeImmutable $updatedAt;

  public function __construct(array $data)
  {
    $this->name = $data['name'];
    $this->nickName = $data['nick_name'];
    $this->email = $data['email'];
    $this->password = $data['password'];
    $this->sex = $data['sex'];
    $this->town = $data['town'];
    $this->country = $data['country'];
    $this->photo = $data['photo'];
    $this->phone = $data['phone'];
    $this->role = $data['role'];
    $this->comments = new ArrayCollection();
    $this->liked = new ArrayCollection();
    $this->signaled = new ArrayCollection();
    $this->articles = new ArrayCollection();
    $this->likeBlogs = new ArrayCollection();
    $this->createdAt = new DateTimeImmutable('now');
    $this->updatedAt = new DateTimeImmutable('now');
  }
  public function jsonSerialize(): array
  {
    // if ($this->comments instanceof PersistentCollection) {
    //   $this->comments->initialize();
    // }
    return [
      'id' => $this->id,
      'name' => $this->name,
      'email' => $this->email,
      'phone' => $this->phone,
      'password' => $this->password,
      'sex' => $this->sex,
      'town' => $this->town,
      'country' => $this->country,
      'photo' => $this->photo,
      'role' => $this->role,
      // 'comments' => $this->comments->toArray(),
      'liked' => $this->liked,
      'signals' => $this->signaled,
      'createdAt' => $this->createdAt->format('Y-m-d H:i:s'),
      'updatedAt' => $this->updatedAt->format('Y-m-d H:i:s')
    ];
  }

  public function getId(): int
  {
    return $this->id;
  }

  public function getUsername(): string
  {
    return $this->name;
  }

  public function getNickname(): string
  {
    return $this->nickName;
  }

  public function getEmail(): string
  {
    return $this->email;
  }

  public function getPhone(): string
  {
    return $this->phone;
  }

  public function getSex(): string
  {
    return $this->sex;
  }

  public function getTown(): string
  {
    return $this->town;
  }

  public function getCountry(): string
  {
    return $this->country;
  }

  public function getPassword(): string
  {
    return $this->password;
  }

  public function getRole(): string
  {
    return $this->role;
  }

  public function getComments(): Collection
  {
    return $this->comments;
  }

  public function getLiked(): Collection
  {
    return $this->liked;
  }

  public function getSignaled(): Collection
  {
    return $this->signaled;
  }

  public function getLikedBlogs(): Collection
  {
    return $this->likeBlogs;
  }

  public function getArticles(): Collection
  {
    return $this->articles;
  }

  public function getCreatedAt(): DateTimeImmutable
  {
    return $this->createdAt;
  }

  public function getUpdatedAt(): DateTimeImmutable
  {
    return $this->updatedAt;
  }

  public function setName(string $name): void
  {
    $this->name = $name;
  }

  public function setNickname(string $nickName): void
  {
    $this->nickName = $nickName;
  }

  public function setEmail(string $email): void
  {
    $this->email = $email;
  }

  public function setPhone(string $phone): void
  {
    $this->phone = $phone;
  }

  public function setSex(string $sex): void
  {
    $this->sex = $sex;
  }

  public function setTown(string $town): void
  {
    $this->town = $town;
  }

  public function setCountry(string $country): void
  {
    $this->country = $country;
  }

  public function setPassword(string $password): void
  {
    $this->password = $password;
  }

  public function setRole(string $role): void
  {
    $this->role = $role;
  }

  public function addComment(CommentSchema $comment): void
  {
    $this->comments->add($comment);
  }

  public function setUpdatedAt(DateTimeImmutable $updatedAt): void
  {
    $this->updatedAt = $updatedAt;
  }

  public function getCreatedAtFormatted(): string
  {
    return $this->createdAt->format('Y-m-d H:i:s');
  }
}
