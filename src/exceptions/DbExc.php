<?php


namespace MvcFramework\Exceptions;

use Exception;
use Throwable;

class DbExc extends Exception
{
    public const CODE_GENERIC_ERROR = 0;
    public const CODE_CONN_ERROR = 1;
    public const CODE_CONN_CLOSED = 2;
    public const CODE_CONN_STIRNG_ERROR = 2;

    public const STR_CONN_CLOSED = "connection was closed";
    /**
     * throw a new DbExc exception
     *
     * @param string $message A message to describe the exception
     * @param integer $code A code usefoul to know what caused the exception
     * `0 => generic exc`
     * `1 => connection error`
     * `2 => connection closed`
     * `3 => connection string error`
     * 
     * @param Throwable|null $previous
     */
    public function __construct(string $message, $code, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function __toString()
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}
