<?php

namespace MvcFramework\Core\Exceptions;

use Exception;
use Throwable;

class ServiceException extends Exception
{
    private ?string $serviceName = null;
    public function __construct(string $message, ?string $serviceName = null, int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->serviceName = $serviceName;
    }

    public function getServiceName()
    {
        return $this->serviceName;
    }

    public function __toString()
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}
