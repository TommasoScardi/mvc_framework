<?php

$dotenv = Dotenv\Dotenv::createImmutable(APP_ROOT);
$dotenv->safeLoad();

//env var for application instance
$dotenv->required(["APP_NAME", "LOG_FILE"]);
$dotenv->required("UPLOAD_MAX_SIZE")->isInteger();

//env var for jwt auth
$dotenv->required("AUTH_TOKEN_DURATION")->isInteger();
$dotenv->required(["AUTH_TOKEN_ISSUER", "AUTH_TOKEN_CREATION_KEY", "AUTH_TOKEN_RENEW_KEY"]);

//env var for db instance
$dotenv->required(['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PWD']);
$dotenv->required(['REDIS_HOST', 'REDIS_PWD']);
