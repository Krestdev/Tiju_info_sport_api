<?php

define('APP_ROOT', __DIR__);

// Load env variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

return [
  'settings' => [
    'slim' => [
      'displayErrorDetails' => true,
      'logErrors' => true,
      'logErrorDetails' => true,
    ],
    'doctrine' => [
      'dev_mode' => true,
      'cache_dir' => APP_ROOT . '/var/doctrine',
      'metadata_dirs' => [APP_ROOT . '/../../src/App/Db/Schema'],
      'connection' => [
        'driver' => 'pdo_mysql',
        'host' => $_ENV['DATABASE_HOSTNAME'], // 'localhost',
        'port' => $_ENV['DATABASE_PORT'], // 3306,
        'dbname' => $_ENV['DATABASE_NAME'], // 'tiju_app',
        'user' => $_ENV['DATABASE_USERNAME'], // 'root',
        'password' => $_ENV['DATABASE_PASSWORD'], // 'Derliche',
        'charset' => 'utf8',
      ],
    ],
  ],
];
