<?php

declare(strict_types=1);

namespace App\Db\Repository;

use App\Db\Schema\AdsSchema;
use App\Db\Schema\ArticleSchema;
use App\Db\Schema\ImageSchema;
use App\Db\Schema\PackageSchema;
use App\Db\Schema\UserSchema;
use Doctrine\ORM\EntityManager;

final class ImageService
{
  private EntityManager $em;

  public function __construct(EntityManager $em)
  {
    $this->em = $em;
  }

  public function createBaseImage(array $image): ImageSchema
  {
    $image = new ImageSchema(null, null, null, $image);
    $this->em->persist($image);
    $this->em->flush();
    return $image;
  }

  public function DeleteBaseImage(int $id)
  {
    $image = $this->em->getRepository(ImageSchema::class)->findOneBy(["id" => $id]);
    $imageData = $image->jsonSerializeDeleted();
    $this->em->remove($image);
    $this->em->flush();
    return $imageData;
  }

  public function createUserProfile(UserSchema $user, array $image): ImageSchema
  {
    $image = new ImageSchema($user, null, null, $image);
    $this->em->persist($image);
    $this->em->flush();
    return $image;
  }

  public function createAdsImage(AdsSchema $ads, array $image): ImageSchema
  {
    $image = new ImageSchema(null, $ads, null, $image);
    $this->em->persist($image);
    $this->em->flush();
    return $image;
  }

  public function addArticleImage(ArticleSchema $article, ImageSchema $image, array $data): ImageSchema
  {
    $image->setLocation($data['location']);
    $image->setArticle($article);
    $this->em->persist($image);
    $this->em->flush();
    return $image;
  }

  public function readAll(): array
  {
    return $this->em->getRepository(ImageSchema::class)->findAll();
  }

  public function findById(int $id): ?ImageSchema
  {
    return $this->em->getRepository(ImageSchema::class)->findOneBy(["id" => $id]);
  }

  public function update(int $id, array $data): ImageSchema
  {
    $image = $this->findById($id);
    $image->setSize($data['size']);
    $image->setLocation($data['location']);
    $this->em->persist($image);
    $this->em->flush();
    return $image;
  }

  public function delete(int $id): ?array
  {
    $image = $this->em->getRepository(ImageSchema::class)->findOneBy(["id" => $id]);
    $imageData = $image->jsonSerializeDeleted();

    if ($image->getUser()) {
      $image->getUser()->setProfile(null);
    }
    if ($image->getAdvertisment()) {
      $image->getAdvertisment()->setImage(null);
    }
    if ($image->getArticle()) {
      $image->getArticle()->removeImage($image);
    }
    $this->em->remove($image);
    $this->em->flush();
    return $imageData;
  }
}
