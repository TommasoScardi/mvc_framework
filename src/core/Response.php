<?php

namespace MvcFramework\Core;

class Response {
    private const RES_CODE_MIN = 100;
    private const RES_CODE_MAX = 600;

    public const RES_OK = 200;
    public const RES_CREATED = 201;
    public const RES_NO_CONTENT = 204;

    public const RES_REDIRECT = 301;
    
    public const RES_BAD_REQ = 400;
    public const RES_UNAUTH = 401;
    public const RES_FORBIDDEN = 403;
    public const RES_NOT_FOUND = 404;

    public const RES_SERVER_ERROR = 400;

    private string $resBuffer = "";
    private int $code = 0;

    /**
     * Get the value of code
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set the value of code
     *
     * @return  self
     */
    public function setCode(int $code)
    {
        if ($code <= self::RES_CODE_MIN || $code >= self::RES_CODE_MAX) {
            $this->code = Response::RES_SERVER_ERROR;
            $this->resBuffer = '{"message": "server error, status code must be between min and max"}';
            return;
        }
        $this->code = $code;

        return $this;
    }

    /**
     * Get the value of resBuffer
     */
    public function getResponse()
    {
        return $this->resBuffer;
    }

    public function write(string $text)
    {
        if ($this->code !== 0) return;
        $this->resBuffer .= $text;
        return $this;
    }

    public function json(mixed $data, int $code = 200)
    {
        if ($this->code !== 0) return;
        $jsonString = json_encode($data);
        if (!$jsonString || empty($jsonString)) {
            $this->code = 500;
        }
        $this->code = $code;
        $this->resBuffer = $jsonString;
    }

    public function end(string $text, int $code = 200)
    {
        if ($this->code !== 0) return;
        $this->resBuffer .= $text;
        $this->code = $code;
    }

    public function error(int $code, ?string $message = null, bool $echoMessage = true, bool $log = false, ?array $context = null) {
        $this->code = $code;
        if ($message != null && $echoMessage) {
            $this->resBuffer = '{"message": "'.$message.'"}';
        }
        if ($message != null && $log) {
            Application::log()->error($message, $context ?? []);
        }
    }
}
