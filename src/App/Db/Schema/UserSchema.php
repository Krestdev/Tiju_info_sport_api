<?php

namespace App\Db\Schema;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;
use Google\Service\RapidMigrationAssessment\Collector;
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

  #[Column(type: 'string', length: 100, unique: true, nullable: false)]
  private string $password;

  #[Column(type: 'string', length: 100)]
  private string $role;

  #[Column(name: "api_key", type: 'string', length: 255, nullable: true)]
  private string|null $api_key;

  #[Column(name: 'api_key_hash', type: 'string', length: 255, nullable: true)]
  private string|null $api_key_hash;

  #[Column(type: "string", length: 255, nullable: true)]
  private ?string $resetToken = null;

  #[Column(type: "datetimetz_immutable", nullable: true)]
  private ?DateTimeImmutable $resetTokenExpiresAt = null;

  #[OneToOne(targetEntity: ImageSchema::class, inversedBy: 'user', cascade: ['persist', 'remove'], orphanRemoval: true)]
  private ?ImageSchema $profile = null;

  /**
   * A user has many comments
   * @var Collection<int, CommentSchema>
   */
  #[OneToMany(targetEntity: CommentSchema::class, mappedBy: 'author', cascade: ['persist'], orphanRemoval: true)]
  private Collection $comments;

  /**
   * Many users like many Comments
   * @var Collection<int, CommentSchema>
   */
  #[ManyToMany(targetEntity: CommentSchema::class, mappedBy: 'likes', cascade: ['persist'], orphanRemoval: false)]
  private Collection $liked;

  /**
   * Many user signal many messages
   * @var Collection<int, CommentSchema>
   */
  #[ManyToMany(targetEntity: CommentSchema::class, mappedBy: 'signals', cascade: ['persist'], orphanRemoval: false)]
  private Collection $signaled;

  // articles

  #[OneToMany(targetEntity: ArticleSchema::class, mappedBy: 'author', cascade: ['persist'], orphanRemoval: false)]
  private Collection $articles;

  #[ManyToMany(targetEntity: ArticleSchema::class, mappedBy: 'likes', cascade: ['persist'], orphanRemoval: false)]
  private Collection $likeBlogs;

  // categories

  #[OneToMany(targetEntity: CategorySchema::class, mappedBy: 'author', cascade: ['persist'], orphanRemoval: false)]
  private Collection $categories;

  // Advertisements

  #[OneToMany(targetEntity: AdsSchema::class, mappedBy: 'author', cascade: ['persist'], orphanRemoval: false)]
  private Collection $advertisements;

  // packages

  #[OneToMany(targetEntity: PackageSchema::class, mappedBy: 'author', cascade: ['persist'], orphanRemoval: false)]
  private Collection $packages;

  /** one Customer has One Subscription. */
  #[oneToMany(targetEntity: SubscriptionSchema::class, mappedBy: 'customer', cascade: ['persist'], orphanRemoval: true)]
  private Collection $subscribed; //One active subscription at a time

  #[OneToMany(targetEntity: PaymentSchema::class, mappedBy: "customer", cascade: ["persist", "remove"])]
  private Collection $payments;

  #[Column(name: "created_at", type: 'datetimetz_immutable', nullable: false)]
  private DateTimeImmutable $created_at; //

  #[Column(name: 'updated_at', type: 'datetimetz_immutable', nullable: false)]
  private DateTimeImmutable $updated_at;

  #[Column(name: 'google_id', type: 'string', length: 255, nullable: true)]
  private string|null $google_id = null;

  public function __construct(array $data)
  {
    $this->name = $data['name'];
    $this->nickName = $data['nick_name'];
    $this->email = $data['email'];
    $this->password = $data['password'];
    $this->sex = $data['sex'];
    $this->town = $data['town'];
    $this->country = $data['country'];
    $this->phone = $data['phone'];
    $this->role = $data['role'];
    $this->api_key = $data['api-key'] ?? null;
    $this->api_key_hash = $data['api-key-hash'] ?? null;
    $this->google_id = $data['google_id'] ?? null;
    $this->comments = new ArrayCollection();
    $this->liked = new ArrayCollection();
    $this->signaled = new ArrayCollection();
    $this->articles = new ArrayCollection();
    $this->likeBlogs = new ArrayCollection();
    $this->categories = new ArrayCollection();
    $this->payments = new ArrayCollection();
    $this->created_at = new DateTimeImmutable('now');
    $this->updated_at = new DateTimeImmutable('now');
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
      'image' => $this->profile,
      'password' => $this->password,
      'sex' => $this->sex,
      'town' => $this->town,
      'country' => $this->country,
      'role' => $this->role,
      // 'api-key' => $this->api_key,
      // 'comments' => $this->comments->toArray(),
      'liked' => $this->liked->count(),
      'signals' => $this->signaled->count(),
      'created_at' => $this->created_at->format('Y-m-d H:i:s'),
      'updated_at' => $this->updated_at->format('Y-m-d H:i:s')
    ];
  }

  public function jsonSerializeDeleted(): array
  {
    return [
      'id' => $this->id,
      'name' => $this->name,
      'email' => $this->email,
      'phone' => $this->phone,
      'image' => $this->profile !== null ? $this->profile->jsonSerializeDeleted() : $this->profile,
      'password' => $this->password,
      'sex' => $this->sex,
      'town' => $this->town,
      'country' => $this->country,
      'role' => $this->role,
      // 'api-key' => $this->api_key,
      // 'comments' => $this->comments->toArray(),
      'liked' => $this->liked->count(),
      'signals' => $this->signaled->count(),
      'created_at' => $this->created_at->format('Y-m-d H:i:s'),
      'updated_at' => $this->updated_at->format('Y-m-d H:i:s')
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

  public function getProfile(): ?ImageSchema
  {
    return $this->profile;
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

  public function getApiKey(): string
  {
    return $this->api_key;
  }

  public function getApiKeyHash(): string
  {
    return $this->api_key_hash;
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

  public function getCategories(): Collection
  {
    return $this->categories;
  }

  public function getSubscription(): Collection
  {
    return $this->subscribed;
  }

  public function getCreatedAt(): DateTimeImmutable
  {
    return $this->created_at;
  }

  public function getUpdated_at(): DateTimeImmutable
  {
    return $this->updated_at;
  }

  public function getGoogleId(): string
  {
    return $this->google_id;
  }

  public function setResetToken(?string $token): void
  {
    $this->resetToken = $token;
    $this->resetTokenExpiresAt = new DateTimeImmutable('+1 hour'); // Token valid for 1 hour
  }

  public function getResetToken(): ?string
  {
    return $this->resetToken;
  }


  public function isResetTokenValid(): bool
  {
    return $this->resetToken !== null && new DateTimeImmutable() < $this->resetTokenExpiresAt;
  }

  public function clearResetToken(): void
  {
    $this->resetToken = null;
    $this->resetTokenExpiresAt = null;
  }

  public function setProfile(?ImageSchema $profile): void
  {
    $this->profile = $profile;
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

  public function setApiKey(string $apiKey): void
  {
    $this->api_key = $apiKey;
  }

  public function setApiKeyHash(string $apiKeyHash): void
  {
    $this->api_key_hash = $apiKeyHash;
  }

  public function addSubscription(?SubscriptionSchema $subscription): void
  {
    $allFailedOrExpired = $this->subscribed->forAll(function ($index, $subscription) {
      $status = $subscription->getStatus();
      $expirationDate = $subscription->expiresOn();
      $currentDate = new DateTimeImmutable();

      return ($status === 'COMPLETED' && $expirationDate < $currentDate) || $status === 'FAILED';
    });

    if ($allFailedOrExpired && !$this->subscribed->contains($subscription)) {
      $this->subscribed->add($subscription);
    }
  }

  public function removeSubscription(SubscriptionSchema $subscription): void
  {
    if ($this->subscribed->contains($subscription)) {
      $this->subscribed->removeElement($subscription);
    }
  }

  public function hasNoActiveSubscription(): bool
  {
    if ($this->subscribed->count() > 0) {
      return $this->subscribed->forAll(function ($index, $subscription) {
        $status = $subscription->getStatus();
        $expirationDate = $subscription->getExpiresOn();
        $currentDate = new DateTimeImmutable();

        return ($status === 'COMPLETED' && $expirationDate < $currentDate) || $status === 'FAILED';
      });
    }
    return true;
  }

  public function addComment(CommentSchema $comment): void
  {
    $this->comments->add($comment);
  }

  public function addArticles(ArticleSchema $article): void
  {
    $this->articles->add($article);
  }

  public function addCategories(CategorySchema $article): void
  {
    $this->categories->add($article);
  }

  public function addAdvertisement(AdsSchema $article): void
  {
    $this->advertisements->add($article);
  }

  public function addPackage(PackageSchema $subscription): void
  {
    $this->packages->add($subscription);
  }

  public function addPayment(PaymentSchema $payment): void
  {
    $this->payments->add($payment);
  }

  public function removePayment(PaymentSchema $payment): void
  {
    if ($this->payments->contains($payment)) {
      $this->payments->removeElement($payment);
    }
  }

  public function setUpdated_at(DateTimeImmutable $updated_at): void
  {
    $this->updated_at = $updated_at;
  }

  public function getCreatedAtFormatted(): string
  {
    return $this->created_at->format('Y-m-d H:i:s');
  }
}
