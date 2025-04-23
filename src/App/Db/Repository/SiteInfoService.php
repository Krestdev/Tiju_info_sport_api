<?php

declare(strict_types=1);

namespace App\Db\Repository;

use App\Db\Schema\SiteInfoSchema;
use App\Db\Schema\UserSchema;
use Doctrine\ORM\EntityManager;

final class SiteInfoService
{
  private EntityManager $em;

  public function __construct(EntityManager $em)
  {
    $this->em = $em;
  }

  public function create(array $siteInfo): SiteInfoSchema
  {
    $siteInfo = new SiteInfoSchema($siteInfo);
    $this->em->persist($siteInfo);
    $this->em->flush();
    return $siteInfo;
  }

  public function createUserProfile(UserSchema $user, array $siteInfo): SiteInfoSchema
  {
    $siteInfo = new SiteInfoSchema($siteInfo);
    $this->em->persist($siteInfo);
    $this->em->flush();
    return $siteInfo;
  }

  public function readAll(): array
  {
    return $this->em->getRepository(SiteInfoSchema::class)->findAll();
  }

  public function findById(int $id): ?SiteInfoSchema
  {
    return $this->em->getRepository(SiteInfoSchema::class)->findOneBy(["id" => $id]);
  }

  public function update(int $id, array $data): SiteInfoSchema
  {
    $siteInfo = $this->findById($id);
    $siteInfo->setDescription($data['description']);
    $siteInfo->setAddress($data['address']);
    isset($data['facebook']) ?? $siteInfo->setFacebook($data['facebook']);
    isset($data['x']) ?? $siteInfo->setX($data['x']);
    isset($data['instagram']) ?? $siteInfo->setInstagram($data['instagram']);
    $siteInfo->setCompany($data['company']);
    $siteInfo->setPhone($data['phone']);
    $siteInfo->setEmail($data['email']);
    $this->em->persist($siteInfo);
    $this->em->flush();
    return $siteInfo;
  }

  public function delete(int $id): ?array
  {
    $siteInfo = $this->em->getRepository(SiteInfoSchema::class)->findOneBy(["id" => $id]);
    $siteInfoData = $siteInfo->jsonSerializeDeleted();

    $this->em->remove($siteInfo);
    $this->em->flush();
    return $siteInfoData;
  }
}
