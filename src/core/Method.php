<?php

namespace MvcFramework\Core;

use Exception;
use Throwable;

use MvcFramework\Core\Request;

class Method
{
    public const METHOD_GET = "get";
    public const METHOD_POST = "post";
    public const METHOD_PATCH = "patch";
    public const METHOD_DELETE ="delete";

    public static function registerMethod(Request $req, string ...$methods)
    {
        $methodUsed = array_filter($methods, function(string $elem) use($req) {
            return $elem === $req->method();
        });

        if(count($methodUsed) > 0) {
            return;
        }
        else {
            throw new UnRegisteredMethodExc("method not allowed with action requested");
        }
    }
}

class UnRegisteredMethodExc extends Exception
{
    public function __construct(string $message, int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function __toString()
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}
