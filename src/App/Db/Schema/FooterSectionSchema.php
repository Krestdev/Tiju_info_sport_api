<?php

declare(strict_types=1);

namespace App\Db\Schema;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;
use JsonSerializable;

#[Entity, Table(name: "sections")]
class FooterSectionSchema implements JsonSerializable
{
  #[Id, Column(type: 'integer'), GeneratedValue(strategy: 'AUTO')]
  private int $id;

  #[OneToMany(targetEntity: ContentSchema::class, mappedBy: 'section', cascade: ['persist'], orphanRemoval: false)]
  private Collection $content;

  #[Column(type: 'string', length: 255)]
  private string $title;

  public function __construct(string $title)
  {
    $this->title = $title;
    $this->content = new ArrayCollection();
  }

  public function getId(): int
  {
    return $this->id;
  }

  public function getContent(): Collection
  {
    return $this->content;
  }

  public function getTitle(): string
  {
    return $this->title;
  }

  public function addContent(ContentSchema $content): void
  {
    if (!$this->content->contains($content)) {
      $this->content->add($content);
    }
  }

  public function setTitle(string $title): void
  {
    $this->title = $title;
  }

  public function jsonSerialize(): array
  {
    return [
      'id' => $this->id,
      'title' => $this->title,
      'content' => $this->content->toArray(),
    ];
  }
}
