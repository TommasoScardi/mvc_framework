<?php

namespace MvcFramework\Core;

class MethodReturn
{
    public bool $status;
    public ?string $message;
    public ?array $data;

    public function setReturn(bool $status, ?string $message = null, ?array $data = null)
    {
        $this->status = $status;
        $this->message = $message;
        $this->data = $data;
        return $this;
    }
}
