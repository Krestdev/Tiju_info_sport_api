<?php

declare(strict_types=1);

namespace App\Db\Repository;

use App\Db\Schema\AdsSchema;
use App\Db\Schema\UserSchema;
use Doctrine\ORM\EntityManager;

final class AdsService
{
  private EntityManager $em;

  public function __construct(EntityManager $em)
  {
    $this->em = $em;
  }

  public function create(UserSchema $user, array $ads): AdsSchema
  {
    $ads = new AdsSchema($user, $ads);
    $this->em->persist($ads);
    $this->em->flush();
    return $ads;
  }

  public function readAll(): array
  {
    return $this->em->getRepository(AdsSchema::class)->findAll();
  }

  public function findById(int $id): ?AdsSchema
  {
    return $this->em->getRepository(AdsSchema::class)->findOneBy(["id" => $id]);
  }

  public function update(int $id, array $data): AdsSchema
  {
    $ads = $this->findById($id);
    $ads->setTitle($data['title']);
    $ads->setUrl($data['url']);
    $ads->setDescription($data['description']);
    $ads->setImage($data['image']);
    $this->em->persist($ads);
    $this->em->flush();
    return $ads;
  }

  public function delete(int $id): ?array
  {
    $ads = $this->em->getRepository(AdsSchema::class)->findOneBy(["id" => $id]);
    $adsData = $ads->jsonSerializeDeleted();
    $this->em->remove($ads);
    $this->em->flush();
    return $adsData;
  }
}
