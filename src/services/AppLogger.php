<?php

namespace MvcFramework\Services;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

use MvcFramework\Core\Application;
use MvcFramework\Core\Service;
use MvcFramework\Core\Exceptions\ServiceException;

/**
 * Class Service to log on files
 */
class AppLogger implements Service
{
    private string $fileName;
    private Logger $logger;

    /**
     * AppLogger CTOR
     *
     * @param string $name name of file
     */
    public function __construct($name = "app.log")
    {
        $this->fileName = $name;
    }

    public function init()
    {
        $this->logger = new Logger("logger");
        $this->logger->pushHandler(new StreamHandler(Application::$ROOT_PATH . "log/".$this->fileName, Logger::WARNING));
        return true;
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
