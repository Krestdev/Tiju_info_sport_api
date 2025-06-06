<?php

declare(strict_types=1);

use App\Controllers\UserController;
use Slim\Factory\AppFactory;
use Slim\Handlers\Strategies\RequestResponseNamedArgs;
use App\Middleware\AddJasonResponseHeader;
use App\Middleware\Authentication\RequireApiKey;

require __DIR__ . '/vendor/autoload.php';

$container = require __DIR__ . '/config/ContainerDI.php';

AppFactory::setContainer($container);
$app = AppFactory::create();

date_default_timezone_set('Africa/Douala'); // Or your preferred timezone

$collector = $app->getRouteCollector();
$collector->setDefaultInvocationStrategy(new RequestResponseNamedArgs);

$app->addBodyParsingMiddleware();

// log errors in jason format
$error_middleware = $app->addErrorMiddleware(true, true, true);
$error_handler = $error_middleware->getDefaultErrorHandler();
$error_handler->forceContentType('application/json');

// $app->options('/{routes:.+}', function ($request, $response) {
//   return $response->withStatus(204); // 204 No Content for preflight
// });

require __DIR__ . '/src/App/Middleware/cors/CorsMiddleware.php';
require __DIR__ . '/src/Routes/routes.php';

$app->run();

// hosting.