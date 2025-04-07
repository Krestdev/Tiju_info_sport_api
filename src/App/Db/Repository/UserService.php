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
    return $this->em->getRepository(UserSchema::class)->findOneBy(['email' => $data['email']]);
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

  public function changeRole(int $id, string $role): ?UserSchema
  {
    $user = $this->em->getRepository(UserSchema::class)->findOneBy(['id' => $id]);
    if ($user) {
      $user->setRole($role);
      $this->em->persist($user);
      $this->em->flush();
      $this->em->refresh($user);
    }
    return $user;
  }

  public function generateResetToken(int $id): ?string
  {
    $user = $this->em->getRepository(UserSchema::class)->findOneBy(['id' => $id]);
    $resetToken = bin2hex(random_bytes(32));
    if ($user) {
      $user->setResetToken($resetToken);
      $this->em->persist($user);
      $this->em->flush();
      $this->em->refresh($user);
    }
    return $resetToken;
  }

  public function resetPassword(UserSchema $user, string $password): ?UserSchema
  {
    // Hash the new password
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    $user->setPassword($hashedPassword);
    $user->clearResetToken();
    $this->em->persist($user);
    $this->em->flush();
    $this->em->refresh($user);
    return $user;
  }

  public function validateResetToken(string $token): ?UserSchema
  {
    return $this->em->getRepository(UserSchema::class)->findOneBy(['reset_token' => $token]);
  }


  public function findById(int $id): ?UserSchema
  {
    return $this->em->getRepository(UserSchema::class)->findOneBy(['id' => $id]);
  }

  public function findByToken(string $token): ?UserSchema
  {
    return $this->em->getRepository(UserSchema::class)->findOneBy(['resetToken' => $token]);
  }

  public function findByGoogleId(string $id): ?UserSchema
  {
    return $this->em->getRepository(UserSchema::class)->findOneBy(['google_id' => $id]);
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
      $userData = $user->jsonSerializeDeleted();
      $this->em->remove($user);
      $this->em->flush();
    }
    return $userData;
  }

  public function findeApiKey(string $apiKey): ?UserSchema
  {
    return $this->em->getRepository(UserSchema::class)->findOneBy(["api_key" => $apiKey]);
  }
}
