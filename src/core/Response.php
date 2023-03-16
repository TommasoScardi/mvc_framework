<?php

namespace MvcFramework\Core;

/**
 * Response class - Handle sever response - contains all request codes
 */
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
     * Get the value of status code
     *
     * @return int
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set the value of status code
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
     * 
     * @return string
     */
    public function getResponse()
    {
        return $this->resBuffer;
    }

    /**
     * Add text to the buffer
     *
     * @param string $text
     * @return string
     */
    public function write(string $text)
    {
        if ($this->code !== 0) return;
        $this->resBuffer .= $text;
        return $this;
    }

    /**
     * Write JSON to the buffer
     *
     * @param mixed $data JSON data
     * @param integer $code response status code
     * @return void
     */
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

    /**
     * Write text to the buffer
     *
     * @param string $text string/data
     * @param integer $code response status code
     * @return void
     */
    public function end(string $text, int $code = 200)
    {
        if ($this->code !== 0) return;
        $this->resBuffer .= $text;
        $this->code = $code;
    }

    /**
     * Handle http errors
     *
     * @param integer $code error status code
     * @param string|null $message message
     * @param boolean $echoMessage bool to determine if print or not the message to the client
     * @param boolean $log bool to determine if log the error or not
     * @param array|null $context error context writed on the log file
     * @return void
     */
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
