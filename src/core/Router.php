<?php

namespace MvcFramework\Core;

use ReflectionClass;

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
        if ($request === false) {
            $this->res->error(404, "Path requested not found!");
            return;
        }
        extract($request);

        $className = "MvcFramework\\Controllers\\" . $controller . "Controller"; 
        if (!class_exists($className)) {
            Application::log()->error("Requested controller class not founded", ["controller" => $controller])
            $this->res->error(404, "Requested controller class not founded");
            return;
        }
        if (!method_exists($className, $action)) {
            Application::log()->error("Requested action not found into controller methods", ["controller" => $controller, "action" => $action]);
            $this->res->error(404, "Requested action $action not found into controller $controller methods");
            return;
        }
        $classConstructorParams = array_map(function($v) {return $v->name;},
                    (new ReflectionClass($className))->getConstructor()->getParameters());

        $unregisteredServices = array_diff($classConstructorParams, array_keys($services));
        if(count($unregisteredServices) > 0) {
            Application::log()->error("The controller constructor request unavailable/unregistered services", ["services_list" => $unregisteredServices]);
            $this->res->error(500, "The controller constructor request unavailable/unregistered services");
            return;
        }

        $depToInject = array_intersect_key($services, array_fill_keys(array_intersect(array_keys($services), $classConstructorParams), null));
        $controllerInstance = new $className(...$depToInject);
        if (call_user_func([$controllerInstance, $action], $this->req, $this->res) === false) {
            Application::log()->error("The action execution resulted in an error OR it returns a value instead using response params",
                                      ["controller" => $controller, "action" => $action]);
            $this->res->error(500, "The action execution resulted in an error OR it returns a value instead using response params");
            return;
        }
    }
}
