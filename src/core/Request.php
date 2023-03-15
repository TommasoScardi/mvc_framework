<?php

namespace MvcFramework\Core;

class Request
{
    private const DEFAULT_CONTROLLER = "Home";
    private const DEFAULT_ACTION = "Index";

    public const CONTROLLER = 0;
    public const ACTION = 1;
    public const ID = 2;

    private string $ID = "";

    public function getID()
    {
        return is_numeric($this->ID) ? (int)$this->ID : $this->ID;
    }

    public function getIP()
    {
        return $_SERVER['REMOTE_ADDR'];
    }

    public function getPath()
    {
        //{controller}/{action}/{id}
        $path = $_SERVER["REQUEST_URI"] ?? '/';
        if (!empty(Application::$SUBROOT_PATH)) {
            $path = str_replace(Application::$SUBROOT_PATH, "", $path);
        }

        $pos = strpos($path, '?');
        if ($pos !== false) {
            $path = substr($path, 0, $pos);
        }

        $pathArray = explode('/', $path);
        $controllerAction = array_values(array_filter($pathArray));
        if (count($controllerAction) === 0) {
            return ["controller" => self::DEFAULT_CONTROLLER, "action" => self::DEFAULT_ACTION];
        }
        else if (count($controllerAction) === 1) {
            return ["controller" => $controllerAction[self::CONTROLLER], "action" => self::DEFAULT_ACTION];
        }
        else if (count($controllerAction) === 2) {
            return ["controller" => $controllerAction[self::CONTROLLER], "action" => $controllerAction[self::ACTION]];
        }
        else if (count($controllerAction) === 3 && (is_numeric($controllerAction[self::ACTION]) || is_string($controllerAction[self::ACTION]))) {
            $this->ID = $controllerAction[self::ID];
            return ["controller" => $controllerAction[self::CONTROLLER], "action" => $controllerAction[self::ACTION]];
        }
        else {
            return false;
        }
    }

    public function method()
    {
        return strtolower($_SERVER["REQUEST_METHOD"]);
    }

    public function isGet() {
        return $this->method() === "get";
    }

    public function isPost()
    {
        return $this->method() === "post";
    }

    private function isJsonBody()
    {
        return apache_request_headers()["content-type"] === "application/json";
    }

    private function getJsonBody()
    {
        $rawJson = file_get_contents('php://input');
        if (empty($rawJson)) {
            return false;
        }

        $jsonData = json_decode($rawJson, true);
        if ($jsonData === null) {
            return false;
        }
        return $jsonData;
    }

    public function getQS()
    {
        $qs = [];

        if ($this->method() === "get"
        || $this->method() === "post"
        || $this->method() === "patch"
        || $this->method() === "delete") {
            foreach ($_GET as $key => $val) {
                $qs[$key] = filter_input(INPUT_GET, $key, FILTER_SANITIZE_SPECIAL_CHARS);
            }
        }

        return $qs;
    }

    public function getBody()
    {
        $body = [];
        if ($this->method() === "post") {
            if ($this->isJsonBody()) {
                $body = filter_var_array($this->getJsonBody(), FILTER_SANITIZE_SPECIAL_CHARS);
            }
            else {
                foreach ($_POST as $key => $value) {
                    $body[$key] = filter_input(INPUT_POST, $key, FILTER_SANITIZE_SPECIAL_CHARS);
                }
            }
        }

        if ($this->method() === "patch") {
            if ($this->isJsonBody()) {
                $body = filter_var_array($this->getJsonBody(), FILTER_SANITIZE_SPECIAL_CHARS);
            }
        }

        return $body;
    }
}
