<?php

declare(strict_types=1);

namespace App\Db\Schema;

use DateTimeImmutable;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;

class ArticleSchema
{
  #[Id, Column(type: 'integer'), GeneratedValue(strategy: 'AUTO')]
  private int $id;

  #[Column(type: 'string', length: 255)]
  private string $type;

  #[Column(type: 'string', length: 255)]
  private string $title;

  #[Column(type: 'string', length: 100)]
  private string $summery;

  #[Column(type: 'string', length: 255)]
  private string $description;
  // private string $media: string[],

  #[OneToMany(targetEntity: CommentSchema::class, mappedBy: 'article')]
  private Collection $commentaire;

  #[OneToMany(targetEntity: UserSchema::class, mappedBy: 'likeBolgs')]
  private Collection $like;

  #[ManyToOne(targetEntity: UserSchema::class, inversedBy: 'articles')]
  private UserSchema|null $user = null;

  #[Column(name: "created_at", type: 'datetimetz_immutable', nullable: false)]
  private DateTimeImmutable $createdAt;

  #[Column(name: 'updated_at', type: 'datetimetz_immutable', nullable: false)]
  private DateTimeImmutable $updatedAt;
  // private string $abonArticle: Abonnement
};
