<?php

namespace App\Db\Repository;

use App\Db\Schema\CommentSchema;
use Doctrine\ORM\EntityManager;
use App\Db\Schema\UserSchema;


final class UserService
{
  private EntityManager $em;
  public function __construct(EntityManager $em)
  {
    $this->em = $em;
  }

  public function signUp(array $data): UserSchema
  {
    $user = new UserSchema($data);
    $this->em->persist($user);
    $this->em->flush();
    $this->em->refresh($user);
    return $user;
  }

  public function signIn(array $data): ?UserSchema
  {
    return $this->em->getRepository(UserSchema::class)->findOneBy(['password' => $data['password'], 'email' => $data['email']]);
  }

  public function signOut(string $email): ?UserSchema
  {
    return $this->em->getRepository(UserSchema::class)->findOneBy(['email' => $email]);
  }

  public function update(int $id, array $data): ?UserSchema
  {
    $user = $this->em->getRepository(UserSchema::class)->findOneBy(['id' => $id]);
    if ($user) {
      $user->setEmail($data['email']);
      $user->setPassword($data['password']);
      $this->em->persist($user);
      $this->em->flush();
      $this->em->refresh($user);
    }
    return $user;
  }

  public function findById(int $id): ?UserSchema
  {
    return $this->em->getRepository(UserSchema::class)->findOneBy(['id' => $id]);
  }

  public function findbyEmail(string $email): ?UserSchema
  {
    return $this->em->getRepository(UserSchema::class)->findOneBy(['email' => $email]);
  }

  /**
   * Summary of readAll
   * @return UserSchema[]
   */
  public function readAll(): ?array
  {
    return $this->em->getRepository(UserSchema::class)->findAll();
  }

  public function delete(int $id): ?array
  {
    $user = $this->em->getRepository(UserSchema::class)->findOneBy(['id' => $id]);
    $userData = null;
    if ($user) {
      $userData = $user->jsonSerialize();
      $this->em->remove($user);
      $this->em->flush();
    }
    return $userData;
  }
}
