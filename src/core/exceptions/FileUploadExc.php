<?php

namespace MvcFramework\Core\Exceptions;

use Exception;
use Throwable;

class FileUploadExc extends Exception
{
    private ?string $fileName = null;
    public function __construct(string $message, ?string $fileName = null, int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->fileName = $fileName;
    }

    public function getFileName()
    {
        return $this->fileName;
    }

    public function __toString()
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}
