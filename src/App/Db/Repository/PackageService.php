<?php

declare(strict_types=1);

namespace App\Db\Repository;

use App\Db\Schema\PackageSchema;
use App\Db\Schema\UserSchema;
use Doctrine\ORM\EntityManager;

final class PackageService
{
  private EntityManager $em;

  public function __construct(EntityManager $em)
  {
    $this->em = $em;
  }

  public function create(UserSchema $user, array $Package): PackageSchema
  {
    $Package = new PackageSchema($user, $Package);
    $this->em->persist($Package);
    $this->em->flush();
    return $Package;
  }

  public function readAll(): array
  {
    return $this->em->getRepository(PackageSchema::class)->findAll();
  }

  public function findById(int $id): ?PackageSchema
  {
    return $this->em->getRepository(PackageSchema::class)->findOneBy(["id" => $id]);
  }

  public function update(int $id, array $data): PackageSchema
  {
    $Package = $this->findById($id);
    $Package->setTitle($data['title']);
    $Package->setPrice($data['price']);
    $this->em->persist($Package);
    $this->em->flush();
    return $Package;
  }

  public function delete(int $id): ?array
  {
    $Package = $this->em->getRepository(PackageSchema::class)->findOneBy(["id" => $id]);
    $PackageData = $Package->jsonSerializeDeleted();
    $this->em->remove($Package);
    $this->em->flush();
    return $PackageData;
  }
}
