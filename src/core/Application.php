<?php

namespace MvcFramework\Core;

use Exception;
use MvcFramework\Exceptions\DbExc;
use MvcFramework\Services\AppLogger;

/**
 * MVC Application EndPoint - Manage all requests and resources (services)
 */
class Application
{
    /**
     * Static Logger for Request ERROR logs
     *
     * @var AppLogger
     */
    private static AppLogger $RequestLogger;

    public static string $ROOT_PATH;
    public static string $SUBROOT_PATH;

    public Request $request;
    public Response $response;
    public Router $router;

    private array $services;
    
    /**
     * Application CTOR
     *
     * @param string $rootPath app absolute path
     * @param string $subPath any subpath in case the app is in a dir
     * @param string $nameLogFile the name of the log Request file
     */
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

    /**
     * It permits to register a service like the dbConnection class
     *
     * @param string $name sevice name
     * @param mixed $service service instance
     * @return void
     */
    public function registerService(string $name, mixed $service)
    {
        $this->services[$name] = $service;
    }

    /**
     * Function to handle all backend requests
     *
     * @return void
     */
    public function run()
    {
        try {
            $this->router->resolve($this->services); //Fills response attribute
        }
        catch(DbExc $dbExc) {
            $this->response->error(500, "DBEXCEPTION:". strval($dbExc), true, true);
        }
        catch(UnRegisteredMethodExc $e) {
            $this->response->error(500, $e->getMessage(), true, true, $this->request->getPath());
        }
        catch(Exception $e) {
            $this->response->error(500, $e->getMessage());
        }
        finally {
            http_response_code($this->response->getCode());
            echo $this->response->getResponse();
        }
    }

    /**
     * Gets the static logger instance
     *
     * @return \Monolog\Logger
     */
    public static function log()
    {
        return self::$RequestLogger->log();
    }
}