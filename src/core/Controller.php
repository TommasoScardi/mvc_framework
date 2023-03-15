<?php

namespace MvcFramework\Core;

use MvcFramework\Core\Request;
use MvcFramework\Core\Response;

abstract class Controller {
    public function __construct()
    { }

    public abstract function Index(Request $req, Response $res);
}