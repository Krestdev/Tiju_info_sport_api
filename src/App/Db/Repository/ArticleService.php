<?php

declare(strict_types=1);

namespace App\Db\Repository;

use App\Db\Schema\ArticleSchema;
use App\Db\Schema\UserSchema;
use Doctrine\ORM\EntityManager;

final class ArticleService
{
  private EntityManager $em;

  public function __construct(EntityManager $em)
  {
    $this->em = $em;
  }

  public function create(UserSchema $user, array $data): ArticleSchema
  {
    $article = new ArticleSchema($user, $data);
    $this->em->persist($article);
    $this->em->flush();
    return $article;
  }

  public function findById(int $id): ?ArticleSchema
  {
    return $this->em->getRepository(ArticleSchema::class)->findOneBy(["id" => $id]);
  }

  public function update(int $id, array $data): ArticleSchema
  {
    $article = $this->findById($id);
    $article->setsummary($data['summary']);
    $article->setDescription($data['description']);
    $article->setTitle($data['title']);
    $this->em->persist($article);
    $this->em->flush();
    return $article;
  }
  public function delete(int $id): ?array
  {
    $article = $this->em->getRepository(ArticleSchema::class)->findOneBy(["id" => $id]);
    $articleData = $article->jsonSerialize();
    $this->em->remove($article);
    $this->em->flush();
    return $articleData;
  }

  public function likeArticle(UserSchema $user, ArticleSchema $article): ArticleSchema
  {
    $article->addLikes($user);
    $this->em->flush();
    return $article;
  }
  public function unlikeArticle(UserSchema $user, ArticleSchema $article): ArticleSchema
  {
    $article->removeLikes($user);
    $this->em->flush();
    return $article;
  }
}
