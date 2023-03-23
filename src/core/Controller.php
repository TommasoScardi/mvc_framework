<?php

namespace MvcFramework\Core;

use MvcFramework\Core\Request;
use MvcFramework\Core\Response;

abstract class Controller
{
    public function __construct()
    {
    }

    public function Index(Request $req, Response $res)
    {
        $res->end("default index action of abstract controller");
    }
}
