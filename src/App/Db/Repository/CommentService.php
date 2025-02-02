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
    $user->getComments()->add($comment);
    $this->em->persist($comment);
    $this->em->flush();
    $this->em->refresh($comment);
    return $comment;
  }

  public function update(int $id, array $data): ?CommentSchema
  {
    $comment = $this->findById($id);
    $comment->setMessage($data["message"]);
    $comment->setUpdatedAt(new DateTimeImmutable('now'));
    $this->em->persist($comment);
    $this->em->flush();
    $this->em->refresh($comment);
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

  public function delete(int $id): ?array
  {
    $comment = $this->em->getRepository(CommentSchema::class)->findOneBy(['id' => $id]);
    $commentData = $comment->jsonSerialize();
    $this->em->remove($comment);
    $this->em->flush();

    return $commentData;
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

  public function reponseToComment(UserSchema $author, CommentSchema $parentComment, array $comment): CommentSchema
  {
    $comment = $this->create($author, $comment);
    $parentComment->addRespond($comment);
    $author->addComment($comment);
    $this->em->flush();

    return $comment;
  }

  public function likeComment(UserSchema $user, CommentSchema $comment): ?CommentSchema
  {
    $comment->addLikes($user);
    $this->em->flush();
    return $comment;
  }

  public function unlikeComment(UserSchema $user, CommentSchema $comment): ?CommentSchema
  {
    $comment->removeLikes($user);
    $this->em->flush();
    return $comment;
  }

  public function signalComment(UserSchema $user, CommentSchema $comment): ?CommentSchema
  {
    $comment->addSignales($user);
    $this->em->flush();
    return $comment;
  }

  public function unsignalComment(UserSchema $user, CommentSchema $comment): ?CommentSchema
  {
    $comment->removeSignales($user);
    $this->em->flush();
    return $comment;
  }
}
