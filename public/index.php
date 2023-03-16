<?php

require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/../config/conf.php";
require_once __DIR__ . "/../config/env.php";

use MvcFramework\Core\Application;
use MvcFramework\Services\AppLogger;
use MvcFramework\Services\DbConn;
use MvcFramework\Services\RedisConn;

$app = new Application(APP_ROOT . URL_ROOT, URL_SUBFOLDER, $_ENV["LOG_FILE"]);
$app->registerService("logger", new AppLogger());
$app->registerService("db", new DbConn($_ENV["DB_HOST"], $_ENV["DB_USER"], $_ENV["DB_PWD"], $_ENV["DB_NAME"]));
// $app->registerService("redis", new RedisConn($_ENV["REDIS_HOST"], $_ENV["REDIS_PWD"]));

$app->run();