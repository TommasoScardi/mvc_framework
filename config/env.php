<?php

$dotenv = Dotenv\Dotenv::createImmutable(APP_ROOT);
$dotenv->safeLoad();
$dotenv->required(['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PWD']);
$dotenv->required(['REDIS_HOST', 'REDIS_PWD']);