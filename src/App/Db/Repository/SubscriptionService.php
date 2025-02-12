<?php

declare(strict_types=1);

namespace App\Db\Repository;

use App\Db\Schema\PackageSchema;
use App\Db\Schema\SubscriptionSchema;
use App\Db\Schema\UserSchema;
use Doctrine\ORM\EntityManager;

final class SubscriptionService
{
  private EntityManager $em;

  public function __construct(EntityManager $em)
  {
    $this->em = $em;
  }

  public function create(UserSchema $user, PackageSchema $package, array $data): SubscriptionSchema
  {
    $data[] = ['status' => 'CREATED'];
    $Subscription = new SubscriptionSchema($user, $package, $data);
    $this->em->persist($Subscription);
    $this->em->flush();
    return $Subscription;
  }

  public function readAll(): array
  {
    return $this->em->getRepository(SubscriptionSchema::class)->findAll();
  }

  public function findById(int $id): ?SubscriptionSchema
  {
    return $this->em->getRepository(SubscriptionSchema::class)->findOneBy(["id" => $id]);
  }

  public function update(int $id, array $data): SubscriptionSchema
  {
    $Subscription = $this->findById($id);
    $Subscription->setExpiresOn($data['expires_on']);
    $this->em->persist($Subscription);
    $this->em->flush();
    return $Subscription;
  }

  public function delete(int $id): ?array
  {
    $subscription = $this->em->getRepository(SubscriptionSchema::class)->findOneBy(["id" => $id]);
    $subscriptionData = $subscription->jsonSerializeDeleted();
    $subscription->customer->removeSubscription($subscription);
    $subscription->setPackage(null);
    $subscription->setCustomer(null);
    $this->em->remove($subscription);
    $this->em->flush();
    return $subscriptionData;
  }
}
