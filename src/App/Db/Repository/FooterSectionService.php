<?php

declare(strict_types=1);

namespace App\Db\Repository;

use App\Db\Schema\FooterSectionSchema;
use Doctrine\ORM\EntityManager;

class FooterSectionService
{
  private EntityManager $em;

  public function __construct(EntityManager $em)
  {
    $this->em = $em;
  }

  public function create(string $title): FooterSectionSchema
  {
    $section = new FooterSectionSchema($title);
    $this->em->persist($section);
    $this->em->flush();
    return $section;
  }

  public function update(int $id, string $title): ?FooterSectionSchema
  {
    $section = $this->findById($id);
    if ($section) {
      $section->setTitle($title);
      $this->em->persist($section);
      $this->em->flush();
      return $section;
    }
    return null;
  }

  public function findById(int $id): ?FooterSectionSchema
  {
    return $this->em->getRepository(FooterSectionSchema::class)->findOneBy(['id' => $id]);
  }

  public function readAll(): array
  {
    return $this->em->getRepository(FooterSectionSchema::class)->findAll();
  }

  public function delete(int $id): ?array
  {
    $section = $this->findById($id);
    if ($section) {
      $sectionData = $section->jsonSerialize();
      $this->em->remove($section);
      $this->em->flush();
      return $sectionData;
    }
    return null;
  }
}
