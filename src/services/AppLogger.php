<?php

namespace MvcFramework\Services;

use Monolog\Level;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use MvcFramework\Core\Application;

/**
 * Class Service to log on files
 */
class AppLogger
{
    private Logger $logger;

    /**
     * AppLogger CTOR
     *
     * @param string $name name of file
     */
    public function __construct($name = "app.log")
    {
        $this->logger = new Logger("logger");
        $this->logger->pushHandler(new StreamHandler(Application::$ROOT_PATH . "log/$name", Level::Warning));
    }

    /**
     * Return logs
     *
     * @return Monolog\Logger
     */
    public function log()
    {
        return $this->logger;
    }
}
