<?php

namespace MvcFramework\Core;

use MvcFramework\Services\AppLogger;

class Application
{
    public static string $ROOT_PATH;
    public static string $SUBROOT_PATH;
    public static AppLogger $RequestLogger;

    public Request $request;
    public Response $response;
    public Router $router;


    public array $services;
    
    public function __construct($rootPath, $subPath)
    {
        self::$ROOT_PATH = $rootPath;
        self::$SUBROOT_PATH = $subPath;
        self::$RequestLogger = new AppLogger("request.log");
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
        $this->router->resolve($this->services); //Fills response attribute
        
        http_response_code($this->response->getCode());
        echo $this->response->getResponse();
    }
}