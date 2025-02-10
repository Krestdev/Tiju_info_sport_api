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
    $package = $this->findById($id);
    $package->setTitle($data['title']);
    $package->setPrice($data['price']);
    $package->setPeriod($data['period']);
    $this->em->persist($package);
    $this->em->flush();
    return $package;
  }

  public function delete(int $id): ?array
  {
    $package = $this->em->getRepository(PackageSchema::class)->findOneBy(["id" => $id]);
    $packageData = $package->jsonSerializeDeleted();
    $this->em->remove($package);
    $this->em->flush();
    return $packageData;
  }
}
