<?php


require_once dirname(__DIR__) . '/vendor/autoload.php';

use DI\Container;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use App\Db\Repository\UserService;

$container = new Container(require __DIR__ . '/db/dbConnection.php');
$container->set(EntityManager::class, static function (Container $c): EntityManager {
  $settings = $c->get('settings');
  $cache = $settings['doctrine']['dev_mode'] ? new ArrayAdapter() : new FilesystemAdapter(directory: $settings['doctrine']['cache_dir']);

  $config = ORMSetup::createAttributeMetadataConfiguration(
    $settings['doctrine']['metadata_dirs'],
    $settings['doctrine']['dev_mode'],
    null,
    $cache
  );

  $connection = DriverManager::getConnection($settings['doctrine']['connection']);

  return new EntityManager($connection, $config);
});
$container->set(UserService::class, function (Container $container) {
  return new UserService($container->get(EntityManager::class));
});

return $container;
