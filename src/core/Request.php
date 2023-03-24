<?php

namespace MvcFramework\Core;

use Exception;
use finfo;
use MvcFramework\Core\Exceptions\FileUploadExc;
use MvcFramework\Core\Exceptions\NotAllowedHttpMethodExc;

/**
 * Request class - store all request data (query sting, body, id)
 */
class Request
{
    private const DEFAULT_CONTROLLER = "Home";
    private const DEFAULT_ACTION = "Index";

    public const CONTROLLER = 0;
    public const ACTION = 1;
    public const ID = 2;

    public const METHOD_GET = "get";
    public const METHOD_POST = "post";
    public const METHOD_PATCH = "patch";
    public const METHOD_DELETE = "delete";

    private string $ID = "";

    /**
     * Gets the ID contained in the query string
     *
     * @return int|string
     */
    public function getID()
    {
        return is_numeric($this->ID) ? (int)$this->ID : $this->ID;
    }

    /**
     * Gets the IP of the client
     *
     * @return string
     */
    public function getIP()
    {
        return $_SERVER['REMOTE_ADDR'];
    }

    public function getReqURL()
    {
        $path = $_SERVER["REQUEST_URI"] ?? '/';
        if (!empty(Application::$SUBDIR))
        {
            $path = str_replace(Application::$SUBDIR, "", $path);
        }

        $pos = strpos($path, '?');
        if ($pos !== false)
        {
            $path = substr($path, 0, $pos);
        }
        return $path;
    }

    /**
     * Parse the requested path into an array of controller, action - false on failure
     *
     * @return array|false ["controller" => controller, "action"=> action]
     */
    public function getPath()
    {
        //{controller}/{action}/{id}
        $pathArray = explode('/', $this->getReqURL());
        $controllerAction = array_values(array_filter($pathArray));

        if (count($controllerAction) === 0)
        {
            return ["controller" => self::DEFAULT_CONTROLLER, "action" => self::DEFAULT_ACTION];
        }
        else if (count($controllerAction) === 1)
        {
            return ["controller" => $controllerAction[self::CONTROLLER], "action" => self::DEFAULT_ACTION];
        }
        else if (count($controllerAction) === 2)
        {
            return ["controller" => $controllerAction[self::CONTROLLER], "action" => $controllerAction[self::ACTION]];
        }
        else if (count($controllerAction) === 3 && (is_numeric($controllerAction[self::ACTION]) || is_string($controllerAction[self::ACTION])))
        {
            $this->ID = $controllerAction[self::ID];
            return ["controller" => $controllerAction[self::CONTROLLER], "action" => $controllerAction[self::ACTION]];
        }
        else
        {
            return false;
        }
    }

    private function getHeaders()
    {
        return array_change_key_case(apache_request_headers());
    }

    /**
     * Gets request method (get, post, delete)
     *
     * @return string
     */
    public function method()
    {
        return strtolower($_SERVER["REQUEST_METHOD"]);
    }

    /**
     * restrict action requests to method registered - throws exc on unregistered method action
     *
     * @param string ...$methods all methods allowed
     * @return void
     * @throws NotAllowedHttpMethodExc if the action is requested with a non registered method
     */
    public function registerMethods(string ...$methods)
    {
        $methodUsed = array_filter($methods, fn (string $elem) => $elem === $this->method());

        if (count($methodUsed) > 0)
        {
            return;
        }
        else
        {
            throw new NotAllowedHttpMethodExc("method " . $this->method() . " not allowed with action requested");
        }
    }

    /**
     * Verify if body is in application/json
     *
     * @return boolean
     */
    private function isJsonBody()
    {
        return $this->getHeaders()["content-type"] === "application/json";
    }

    /**
     * Verify if a file upload is incoming
     *
     * @return boolean
     */
    private function isFileUploading()
    {
        return explode(';', $this->getHeaders()["content-type"])[0] === "multipart/form-data";
    }

    /**
     * Gets the JSON body, before call verify if body is in json
     *
     * @return array
     */
    private function getJsonBody()
    {
        $rawJson = file_get_contents('php://input');
        if (empty($rawJson))
        {
            return false;
        }

        $jsonData = json_decode($rawJson, true);
        if ($jsonData === null)
        {
            return false;
        }
        return $jsonData;
    }

    /**
     * Gets sanitized query string as assoc array 
     *
     * @return array
     */
    public function getQS()
    {
        $qs = [];

        if (
            $this->method() === self::METHOD_GET
            || $this->method() === self::METHOD_POST
            || $this->method() === self::METHOD_PATCH
            || $this->method() === self::METHOD_DELETE
        )
        {
            foreach ($_GET as $key => $val)
            {
                $qs[$key] = filter_input(INPUT_GET, $key, FILTER_SANITIZE_SPECIAL_CHARS);
            }
        }

        return $qs;
    }

    /**
     * Gets the request body (json, form-encoded)
     *
     * @return array
     */
    public function getBody()
    {
        $body = [];
        if ($this->method() === self::METHOD_POST)
        {
            if ($this->isJsonBody())
            {
                $body = filter_var_array($this->getJsonBody(), FILTER_SANITIZE_SPECIAL_CHARS);
            }
            else
            {
                foreach ($_POST as $key => $value)
                {
                    $body[$key] = filter_input(INPUT_POST, $key, FILTER_SANITIZE_SPECIAL_CHARS);
                }
            }
        }

        if ($this->method() === self::METHOD_PATCH)
        {
            if ($this->isJsonBody())
            {
                $body = filter_var_array($this->getJsonBody(), FILTER_SANITIZE_SPECIAL_CHARS);
            }
        }

        return $body;
    }

    /**
     * Handle the HTTP file upload
     * @param string $inputName the name of the HTML field that uploads the file
     * @param string $fileName the new filename
     * @param null|array $allowExt the extension allowed like ["jpeg", "jpg", "png", "gif"]
     * @param string $path the relative dir to upload file, is better if is already created
     * @return false|string false on error otherwise it returns the new file name
     * @throws FileUploadExc 
     * @throws Exception 
     */
    public function saveFileUpload(string $inputName, string $fileName, ?array $allowExt =null, string $path = "public/uploads/")
    {
        if (!$this->isFileUploading())
        {
            throw new FileUploadExc("file uploading not declared in request headers");
        }

        if ($_FILES[$inputName]["error"] != UPLOAD_ERR_OK)
        {
            throw new FileUploadExc("upload error, see code for details", null, $_FILES[$inputName]["error"]);
        }

        if (!is_uploaded_file($_FILES[$inputName]["tmp_name"]))
        {
            throw new FileUploadExc("the file came not from an upload", $_FILES[$inputName]["tmp_name"]);
        }

        if ($_FILES[$inputName]["size"] > UPLOAD_MAX_SIZE)
        {
            throw new FileUploadExc("exceeded file size limit", $_FILES[$inputName]["tmp_name"]);
        }

        $fileExt = (new finfo(FILEINFO_EXTENSION))->file($_FILES[$inputName]["tmp_name"]);
        if ($allowExt !== null && !empty($allowExt))
        {
            if (empty(array_filter($allowExt, fn($var) => $var === $fileExt)))
            {
                throw new FileUploadExc("extension not allowed => $fileExt", $_FILES[$inputName]["tmp_name"]);
            }
        }

        
        $guid = Application::getGUID();
        if (!$guid)
        {
            throw new Exception("generate GUID failed");
        }
        
        $saveFileName = $guid . "_" . $fileName . "." .$fileExt;
        $uploadDir = Application::$ROOT_PATH . $path;
        $uploadPath = $uploadDir . $saveFileName;
        if (!is_dir($uploadDir))
        {
            throw new FileUploadExc("no dir found under $uploadDir", $uploadDir);
        }

        if (!move_uploaded_file($_FILES[$inputName]["tmp_name"], $uploadPath))
        {
            return false;
        }
        else
        {
            return $saveFileName;
        }
    }
}
