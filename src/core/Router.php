<?php

namespace MvcFramework\Core;

use ReflectionClass;
use ReflectionMethod;

/**
 * Router class - Parse and route all the requests
 */
class Router
{
    public Request $req;
    public Response $res;

    /**
     * Router CTOR
     *
     * @param Request $request
     * @param Response $response
     */
    function __construct(Request $request, Response $response)
    {
        $this->req = $request;
        $this->res = $response;
    }

    /**
     * Parse URL path and determine witch controller and action are requested
     *
     * @param array $services the service array
     * @return void
     */
    public function resolve(array $services)
    {
        $request = $this->req->getPath(); //returns array with controller and action keys of false on failure
        if ($request === false)
        {
            $this->res->error(404, "Path requested not found!");
            return;
        }
        extract($request);

        $className = "MvcFramework\\Controllers\\" . $controller . "Controller";
        if (!class_exists($className))
        {
            Application::log()->error("HTTP-404: Requested controller class not found", ["controller" => $controller]);
            $this->res->error(404, "Requested controller class not found");
            return;
        }
        if (!method_exists($className, $action))
        {
            Application::log()->error("HTTP-404: Requested action not found into controller methods", ["controller" => $controller, "action" => $action]);
            $this->res->error(404, "Requested action $action not found into controller $controller methods");
            return;
        }
        else if (!(new ReflectionMethod($className, $action))->isPublic())
        {
            Application::log()->error("HTTP-401: Requested private Controller Method", ["controller" => $controller, "action" => $action]);
            $this->res->error(404, "Requested action $action not found into controller $controller methods");
            return;
        }

        $controllerConstructorParams = array_map( fn($v) => $v->name,
            (new ReflectionClass($className))->getConstructor()->getParameters()
        );
        $unregisteredServices = array_diff($controllerConstructorParams, array_keys($services));
        if (count($unregisteredServices) > 0)
        {
            Application::log()->error("HTTP-500: The controller constructor requested unavailable/unregistered services", ["services_list" => $unregisteredServices, "controller" => $controller, "action" => $action]);
            $this->res->error(500);
            return;
        }

        //the services requested by the controller => dependency
        $depToInject = array_intersect_key($services, array_fill_keys(array_intersect(array_keys($services), $controllerConstructorParams), null));
        //init of all requested services
        foreach ($depToInject as $service)
        { 
            $serviceInterfaces = (new ReflectionClass($service))->getInterfaceNames();
            $a = array_filter($serviceInterfaces, fn($v) => Service::class == $v);
            if (count($a) == 0)
            {
                Application::log()->error("HTTP-500: The controller constructor requested serivces that not implements Service interface", ["service" => $service::class, "controller" => $controller, "action" => $action]);
                $this->res->error(500);
                return;
            }
            if (!call_user_func([$service, "init"], []))
            {
                Application::log()->error("HTTP-500: Unable to init the service required by the controller", ["service" => $service, "controller" => $controller, "action" => $action]);
                $this->res->error(500);
                return;
            }
        }

        $controllerInstance = new $className(...$depToInject);
        if (call_user_func([$controllerInstance, $action], $this->req, $this->res) === false)
        {
            Application::log()->error(
                "The action execution resulted in an error OR it returns a value instead using response params",
                ["controller" => $controller, "action" => $action]
            );
            $this->res->error(500, "The action execution resulted in an error OR it returns a value instead using response params");
            return;
        }
    }
}
