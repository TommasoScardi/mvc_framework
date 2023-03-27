<?php

namespace MvcFramework\Core;

class MethodReturn
{
    public bool $status;
    public ?string $message;

    public function __construct(bool $status = true, ?string $message = null)
    {
        $this->status = $status;
        $this->message = $message;
    }
}
