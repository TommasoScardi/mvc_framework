<?php

define("APP_ROOT", dirname(__DIR__) . "/");
define("APP_SUBDIR", "mvc_framework");

date_default_timezone_set("Europe/Rome");

require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/../config/conf.php";
require_once __DIR__ . "/../config/env.php";

use MvcFramework\Core\Application;
use MvcFramework\Services\AppLogger;
use MvcFramework\Services\DbConn;
use MvcFramework\Services\Mailer;
use MvcFramework\Services\RedisConn;

$app = new Application($_ENV["APP_NAME"], APP_ROOT, APP_SUBDIR, $_ENV["UPLOAD_MAX_SIZE"] , $_ENV["LOG_FILE"]);
$app->registerService("logger", new AppLogger());
$app->registerService("db", new DbConn($_ENV["DB_HOST"], $_ENV["DB_USER"], $_ENV["DB_PWD"], $_ENV["DB_NAME"]));
$app->registerService("redis", new RedisConn($_ENV["REDIS_HOST"], $_ENV["REDIS_PWD"]));
$app->registerService("mailSender", new Mailer($_ENV["MAIL_SENDER"], $_ENV["MAIL_HOSTNAME"], $_ENV["MAIL_USER"], $_ENV["MAIL_PWD"]));

$app->run();
