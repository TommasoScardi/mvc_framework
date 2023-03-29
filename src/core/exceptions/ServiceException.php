<?php

namespace MvcFramework\Core\Exceptions;

use Exception;
use Throwable;

class ServiceException extends Exception
{
    private ?string $serviceName;
    private array $context;
    public function __construct(string $message, ?string $serviceName = null, int $code = 0, array $context = [], Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->serviceName = $serviceName;
        $this->context = $context;
    }

    public function getServiceName()
    {
        return $this->serviceName;
    }

    public function getContext()
    {
        return $this->context;
    }

    public function __toString()
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}
