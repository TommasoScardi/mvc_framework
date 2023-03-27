<?php

namespace MvcFramework\Core\Exceptions;

use RuntimeException;
use Throwable;

class ApplicationExc extends RuntimeException
{
    private array $context = [];
    public function __construct(string $message, int $code = 0, array $context = [], Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
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
