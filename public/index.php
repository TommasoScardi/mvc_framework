<?php

require_once __DIR__ . "/../config/conf.php";
require_once __DIR__ . "/../vendor/autoload.php";

use MvcFramework\Core\Application;
use MvcFramework\Services\AppLogger;

$app = new Application(APP_ROOT . URL_ROOT, URL_SUBFOLDER);
$app->registerService("logger", new AppLogger());

$app->run();