<?php

namespace App\Db\Repository;

use App\Db\Schema\CommentSchema;
use Doctrine\ORM\EntityManager;
use App\Db\Schema\UserSchema;
use DateTimeImmutable;

final class CommentService
{
  private EntityManager $em;
  public function __construct(EntityManager $em)
  {
    $this->em = $em;
  }

  public function create(UserSchema $user, array $data): CommentSchema
  {
    $comment = new CommentSchema($data);
    $comment->setAuthor($user);
    $this->em->persist($comment);
    $this->em->flush();
    $this->em->refresh($comment);
    return $comment;
  }

  public function update(int $id, array $data): ?CommentSchema
  {
    $comment = $this->findById($id);
    if ($comment) {
      $comment->setMessage($data["message"]);
      $comment->setUpdatedAt(new DateTimeImmutable('now'));
      $this->em->persist($comment);
      $this->em->flush();
      $this->em->refresh($comment);
    }
    return $comment;
  }

  public function findById(int $id): ?CommentSchema
  {
    return $this->em->getRepository(CommentSchema::class)->findOneBy(['id' => $id]);
  }

  public function readAll(): ?array
  {
    return $this->em->getRepository(CommentSchema::class)->findAll();
  }

  public function delete(int $id): ?CommentSchema
  {
    $comment = $this->em->getRepository(CommentSchema::class)->findOneBy(['id' => $id]);
    if ($comment) {
      $this->em->remove($comment);
      $this->em->flush();
    }
    return $comment;
  }

  // public function newComment(int $id, CommentSchema $comment): CommentSchema
  // {
  //   $user = $this->em->getRepository(UserSchema::class)->findOneBy(['id' => $id]);

  //   if ($user) {
  //     $user->getComments()->add($comment);
  //     $this->em->flush();
  //   }

  //   return $comment;
  // }

  public function reponseComent(int $id, CommentSchema $comment): CommentSchema
  {
    $user = $this->em->getRepository(UserSchema::class)->findOneBy(['id' => $id]);

    if ($user) {
      $user->getComments()->add($comment);
      $this->em->flush();
    }

    return $comment;
  }
}
