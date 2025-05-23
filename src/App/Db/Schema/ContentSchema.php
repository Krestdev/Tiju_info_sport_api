<?php

declare(strict_types=1);

namespace App\Db\Schema;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use JsonSerializable;

#[Entity, Table(name: "footer_content")]
class ContentSchema implements JsonSerializable
{
  #[Id, Column(type: 'integer'), GeneratedValue(strategy: 'AUTO')]
  private int $id;

  #[ManyToOne(targetEntity: FooterSectionSchema::class, inversedBy: 'content', cascade: ['persist'])]
  private FooterSectionSchema $section;

  #[Column(type: 'string', length: 255)]
  private string $title;

  #[Column(type: 'string', length: 255)]
  private string $url;

  #[Column(type: 'text')]
  private string $content;

  #[Column(type: 'integer')]
  private int $catid;

  public function __construct(FooterSectionSchema $section, string $title, string $url, string $content, int $catid)
  {
    $this->section = $section;
    $this->title = $title;
    $this->catid = $catid;
    $this->url = $url;
    $this->content = $content;

    $section->addContent($this);
  }

  public function getId(): int
  {
    return $this->id;
  }

  public function getCatId(): int
  {
    return $this->catid;
  }

  public function getSection(): FooterSectionSchema
  {
    return $this->section;
  }

  public function getTitle(): string
  {
    return $this->title;
  }

  public function getUrl(): string
  {
    return $this->url;
  }

  public function setSection(FooterSectionSchema $section): void
  {
    $this->section = $section;
  }

  public function setCatId(int $catid): void
  {
    $this->catid = $catid;
  }
  public function setTitle(string $title): void
  {
    $this->title = $title;
  }

  public function setUrl(string $url): void
  {
    $this->url = $url;
  }

  public function setContent(string $content): void
  {
    $this->content = $content;
  }

  public function jsonSerialize(): array
  {
    return [
      'id' => $this->id,
      'section' => $this->section->getId(),
      'content' => $this->content,
      'catid' => $this->catid,
      'title' => $this->title,
      'url' => $this->url,
    ];
  }
}
