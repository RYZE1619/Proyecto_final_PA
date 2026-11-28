<?php

use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../app/global/database_config.php';

$cors = require __DIR__ . '/../app/Middlewares/cors.php';
$auth = require __DIR__ . '/../app/Middlewares/auth.php';
$vehiculos_routers = require __DIR__ . '/../app/Presentation/Routers/endpoints.php';

$app = AppFactory::create();

$app->add($auth);
$cors($app);
$vehiculos_routers($app);

$app->run();
