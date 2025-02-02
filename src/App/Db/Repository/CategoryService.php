<?php

declare(strict_types=1);

namespace App\Db\Repository;

use App\Db\Schema\CategorySchema;
use App\Db\Schema\UserSchema;
use Doctrine\ORM\EntityManager;

final class CategoryService
{
  private EntityManager $em;

  public function __construct(EntityManager $em)
  {
    $this->em = $em;
  }


  public function create(UserSchema $user, array $data): CategorySchema
  {
    $article = new CategorySchema($user, $data);
    $this->em->persist($article);
    $this->em->flush();
    return $article;
  }

  public function readAll(): array
  {
    return $this->em->getRepository(CategorySchema::class)->findAll();
  }

  public function findById(int $id): ?CategorySchema
  {
    return $this->em->getRepository(CategorySchema::class)->findOneBy(["id" => $id]);
  }

  public function update(int $id, array $data): CategorySchema
  {
    $category = $this->findById($id);
    $category->setTitle($data['title']);
    $category->setDescription($data['description']);
    $category->setImage($data['image']);
    $this->em->persist($category);
    $this->em->flush();
    return $category;
  }

  public function delete(int $id): ?array
  {
    $article = $this->em->getRepository(CategorySchema::class)->findOneBy(["id" => $id]);
    $articleData = $article->jsonSerializeDeleted();
    $this->em->remove($article);
    $this->em->flush();
    return $articleData;
  }
}
