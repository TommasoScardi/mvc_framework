<?php

namespace MvcFramework\Core;

use Exception;
use MvcFramework\Exceptions\DbExc;
use MvcFramework\Exceptions\HttpExc;
use MvcFramework\Services\AppLogger;

class Application
{
    private static AppLogger $RequestLogger;

    public static string $ROOT_PATH;
    public static string $SUBROOT_PATH;

    public Request $request;
    public Response $response;
    public Router $router;

    public array $services;
    
    public function __construct(string $rootPath, string $subPath, string $nameLogFile)
    {
        self::$ROOT_PATH = $rootPath;
        self::$SUBROOT_PATH = $subPath;
        self::$RequestLogger = new AppLogger($nameLogFile);
        $this->request = new Request();
        $this->response = new Response();
        $this->router = new Router($this->request, $this->response);
        $this->services = [];
    }

    public function registerService(string $name, mixed $service) {
        $this->services[$name] = $service;
    }

    public function run()
    {
        try {
            $this->router->resolve($this->services); //Fills response attribute
        }
        catch(DbExc $dbExc) {
            $this->response->error(500, "DBEXCEPTION:". strval($dbExc), true, true);
        }
        catch(Exception $e) {
            $this->response->error(500, $e->getMessage());
        }
        finally {
            http_response_code($this->response->getCode());
            echo $this->response->getResponse();
        }
    }

    public static function log()
    {
        return self::$RequestLogger->log();
    }
}