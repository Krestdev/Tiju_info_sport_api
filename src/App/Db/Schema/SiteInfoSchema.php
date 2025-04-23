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

#[Entity, Table(name: "site_info")]
class SiteInfoSchema implements JsonSerializable
{
  #[Id, Column(type: 'integer'), GeneratedValue(strategy: 'AUTO')]
  private int $id;

  #[Column(type: 'string', length: 255, nullable: true)]
  private ?string $company;

  #[Column(type: 'string', length: 255, nullable: true)]
  private string $email;

  #[Column(type: 'string', length: 255, nullable: true)]
  private ?string $phone;

  #[Column(type: 'string', length: 255, nullable: true)]
  private string $address;

  #[Column(type: 'string', length: 255, nullable: true)]
  private ?string $facebook;

  #[Column(type: 'string', length: 255, nullable: true)]
  private ?string $instagram;

  #[Column(type: 'string', length: 255, nullable: true)]
  private ?string $x;

  #[Column(type: 'text', nullable: true)]
  private string $description;

  #[Column(type: 'string', nullable: true)]
  private string $imageurl;

  #[Column(name: "created_at", type: 'datetimetz_immutable', nullable: false)]
  private DateTimeImmutable $createdAt;

  #[Column(name: 'updated_at', type: 'datetimetz_immutable', nullable: false)]
  private DateTimeImmutable $updatedAt;

  public function __construct(array $data)
  {
    $this->phone = $data["phone"];
    $this->address = $data["address"];
    $this->facebook = $data["facebook"] ?? null;
    $this->instagram = $data["instagram"] ?? null;
    $this->x = $data["x"] ?? null;
    $this->imageurl = $data["imageurl"];
    $this->description = $data["description"];
    $this->createdAt = new DateTimeImmutable();
    $this->updatedAt = new DateTimeImmutable();
  }

  public function jsonSerialize(): array
  {
    return [
      'id' => $this->id,
      'company' => $this->company,
      'phone' => $this->phone,
      'email' => $this->email,
      'imageurl' => $this->imageurl,
      'address' => $this->address,
      'facebook' => $this->facebook,
      'instagram' => $this->instagram,
      'x' => $this->x,
      'description' => $this->description,
      'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
      'updated_at' => $this->updatedAt->format('Y-m-d H:i:s')
    ];
  }

  public function jsonSerializeDeleted(): array
  {
    return [
      'id' => $this->id,
      'company' => $this->company,
      'address' => $this->address,
      'facebook' => $this->facebook,
      'instagram' => $this->instagram,
      'x' => $this->x,
      'description' => $this->description,
      'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
      'updated_at' => $this->updatedAt->format('Y-m-d H:i:s')
    ];
  }

  public function getId(): int
  {
    return $this->id;
  }

  public function getimageurl(): ?string
  {
    return $this->imageurl;
  }

  public function getPhone(): ?string
  {
    return $this->phone;
  }

  public function getEmail(): ?string
  {
    return $this->email;
  }

  public function getCompany(): ?string
  {
    return $this->company;
  }

  public function getAddress(): ?string
  {
    return $this->address;
  }

  public function getfacebook(): ?string
  {
    return $this->facebook;
  }

  public function getInstagram(): ?string
  {
    return $this->instagram;
  }

  public function getDescription(): string
  {
    return $this->description;
  }

  public function getCreatedAt(): DateTimeImmutable
  {
    return $this->createdAt;
  }

  public function getUpdatedAt(): DateTimeImmutable
  {
    return $this->updatedAt;
  }


  public function setimageurl(string $imageurl): void
  {
    $this->imageurl = $imageurl;
  }

  public function setDescription(string $description): void
  {
    $this->description = $description;
  }

  public function setPhone(string $phone): void
  {
    $this->phone = $phone;
  }

  public function setEmail(string $email): void
  {
    $this->email = $email;
  }

  public function setInstagram(string $instagram): void
  {
    $this->instagram = $instagram;
  }

  public function setFacebook(string $facebook): void
  {
    $this->facebook = $facebook;
  }

  public function setAddress(string $address): void
  {
    $this->address = $address;
  }

  public function setCompany(string $company): void
  {
    $this->company = $company;
  }

  public function setX(string $x): void
  {
    $this->x = $x;
  }

  public function setUpdatedAt(DateTimeImmutable $updatedAt): void
  {
    $this->updatedAt = $updatedAt;
  }
}
