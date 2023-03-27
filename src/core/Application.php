<?php

namespace MvcFramework\Core;

use MvcFramework\Core\Exceptions\NotAllowedHttpMethodExc;
use MvcFramework\Core\Exceptions\FileUploadExc;
use MvcFramework\Core\Exceptions\ServiceException;

use MvcFramework\Services\AppLogger;

use Exception;
use MvcFramework\Core\Exceptions\ApplicationExc;
use RuntimeException;

/**
 * MVC Application EndPoint - Manage all requests and resources (services)
 */
class Application
{
    public static string $NAME;
    public static string $ROOT_PATH;
    public static string $SUBDIR;
    public static int $UPLOADS_MAX_SIZE;
    
    /**
     * Static Logger for Request ERROR logs
     *
     * @var AppLogger
     */
    private static AppLogger $RequestLogger;

    public Request $request;
    public Response $response;
    public Router $router;

    private array $services;

    /**
     * Application CTOR
     *
     * @param string $rootPath app absolute path
     * @param string $subDir any subpath in case the app is in a dir
     * @param string $nameLogFile the name of the log Request file
     */
    public function __construct(string $appName, string $rootPath, string $subDir, int $maxUploadSize, string $nameLogFile)
    {
        self::$NAME = $appName;
        self::$ROOT_PATH = $rootPath;
        self::$SUBDIR = $subDir;
        self::$UPLOADS_MAX_SIZE = $maxUploadSize;
        self::$RequestLogger = new AppLogger($nameLogFile, true);
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
        try
        {
            $this->router->resolve($this->services); //Fills response attribute
        }
        catch (ServiceException $serviceExc)
        {
            self::log()->error(
                "EXC-SERVICE: " . $serviceExc->getMessage(),
                ["code" => $serviceExc->getCode(), "service" => $serviceExc->getServiceName()]
            );
            $this->response->error(500);
        }
        catch (FileUploadExc $fileExc)
        {
            self::log()->error(
                "EXC-FILEUPLOAD: " . $fileExc->getMessage(),
                ["req_url" => $this->request->getReqURL(), "file_name" => $fileExc->getFileName(), "err_code" => $fileExc->getCode()]
            );
            $this->response->error(500);
        }
        catch (NotAllowedHttpMethodExc $e)
        {
            self::log()->error(
                "HTTP-405: " . $e->getMessage(),
                ["req_url" => $this->request->getReqURL()]
            );
            $this->response->error(405);
        }
        catch (ApplicationExc $e)
        {
            self::log()->error("EXC-APP: " . $e->getMessage(), $e->getContext());
            if ($e->getCode() >= 400 && $e->getCode() < 500)
            {
                $this->response->error($e->getCode(), $e->getMessage());
            }
            else
            {
                $this->response->error(500);
            }
        }
        catch (Exception|RuntimeException $e)
        {
            self::log()->error("EXC: " . $e->getMessage());
            $this->response->error(500);
        }
        finally
        {
            http_response_code($this->response->getCode());
            echo $this->response->getResponse();
        }
    }

    public static function getGUID()
    {
        if (function_exists('com_create_guid'))
        {
            return com_create_guid();
        }
        else
        {
            mt_srand((float)microtime() * 10000); //optional for php 4.2.0 and up.
            $charid = strtolower(md5(uniqid(rand(), true)));
            $hyphen = chr(45); // "-"
            $uuid = substr($charid, 0, 8) . $hyphen
                . substr($charid, 8, 4) . $hyphen
                . substr($charid, 12, 4) . $hyphen
                . substr($charid, 16, 4) . $hyphen
                . substr($charid, 20, 12);
            return $uuid;
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
