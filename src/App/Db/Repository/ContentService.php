<?php

declare(strict_types=1);

namespace App\Db\Repository;

use App\Db\Schema\ContentSchema;
use App\Db\Schema\FooterSectionSchema;
use Doctrine\ORM\EntityManager;

class ContentService
{
  private EntityManager $em;

  public function __construct(EntityManager $em)
  {
    $this->em = $em;
  }

  public function create(FooterSectionSchema $footerSection, array $data): ContentSchema
  {
    $content = new ContentSchema($footerSection, $data['title'], $data['url'], $data['content'], (int)$data['catid']);
    $this->em->persist($content);
    $this->em->flush();
    return $content;
  }

  public function update(int $id, array $data): ?ContentSchema
  {
    $content = $this->findById($id);
    if ($content) {
      $content->setTitle($data['title']);
      $content->setUrl($data['url']);
      $content->setContent($data['content']);
      $content->setCatId($data['catid']);
      $this->em->persist($content);
      $this->em->flush();
      return $content;
    }
    return null;
  }

  public function findById(int $id): ?ContentSchema
  {
    return $this->em->getRepository(ContentSchema::class)->findOneBy(['id' => $id]);
  }
  public function readAll(): array
  {
    return $this->em->getRepository(ContentSchema::class)->findAll();
  }

  public function delete(int $id): ?array
  {
    $content = $this->findById($id);
    if ($content) {
      $contentData = $content->jsonSerialize();
      $this->em->remove($content);
      $this->em->flush();
      return $contentData;
    }
    return null;
  }
}
