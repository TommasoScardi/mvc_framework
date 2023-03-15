<?php

namespace MvcFramework\Core;

use ReflectionClass;

class Router
{
    public Request $req;
    public Response $res;

    function __construct(Request $request, Response $response)
    {
        $this->req = $request;
        $this->res = $response;
    }

    public function resolve(array $services)
    {
        $request = $this->req->getPath(); //returns array with controller and action keys of false on failure
        if ($request === false) {
            Application::$RequestLogger->getLogger()->error('Requested path in a strange format');
            $this->res->error(404);
            return;
        }
        extract($request);

        $className = "MvcFramework\\Controllers\\" . $controller . "Controller"; 
        if (!class_exists($className)) {
            Application::$RequestLogger->getLogger()->error('Requested controller class not founded', ['controller' => $controller]);
            $this->res->error(404, 'controller '. $controller.' not found');
            return;
        }
        if (!method_exists($className, $action)) {
            Application::$RequestLogger->getLogger()->error('Requested action not founded into controller methods', ['controller' => $controller, 'action' => $action]);
            $this->res->error(404, 'action '. $action.' not found under '.$controller.' controller');
            return;
        }
        $classConstructorParams = array_map(function($v) {return $v->name;},
                    (new ReflectionClass($className))->getConstructor()->getParameters());

        $unregisteredServices = array_diff($classConstructorParams, array_keys($services));
        if(count($unregisteredServices) > 0) {
            Application::$RequestLogger->getLogger()->error('The controller constructor request unavailable/unregistered services', ['services_list' => $unregisteredServices]);
            $this->res->error(500);
            return;
        }

        $depToInject = array_intersect_key($services, array_fill_keys(array_intersect(array_keys($services), $classConstructorParams), null));
        $controllerInstance = new $className(...$depToInject);
        if (call_user_func([$controllerInstance, $action], $this->req, $this->res) === false) {
            Application::$RequestLogger->getLogger()->error('The action execution resulted in an error OR it returns a value instead using response params', ['controller' => $controller, 'action' => $action]);
            $this->res->error(500);
            return;
        }
    }
}