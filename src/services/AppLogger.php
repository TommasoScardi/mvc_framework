<?php

namespace MvcFramework\Services;

use Monolog\Level;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use MvcFramework\Core\Application;

class AppLogger
{
    private Logger $logger;

    public function __construct($name = "app.log")
    {
        $this->logger = new Logger("logger");
        $this->logger->pushHandler(new StreamHandler(Application::$ROOT_PATH . "log/$name", Level::Warning));
    }

    public function log()
    {
        return $this->logger;
    }
}
