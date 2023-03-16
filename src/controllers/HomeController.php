<?php

namespace MvcFramework\Controllers;

use MvcFramework\Core\Controller;
use MvcFramework\Core\Request;
use MvcFramework\Core\Response;
use MvcFramework\Services\AppLogger;

class HomeController extends Controller
{
    private readonly AppLogger $logger;
    public function __construct(AppLogger $logger)
    {
        $this->logger = $logger;
    }

    public function Index(Request $req, Response $res)
    {
        $res->end("hello world");
    }
    
    public function Log(Request $req, Response $res)
    {
        $res->end("logging to file " . $req->getIP() . " id => ".$req->getID());
        $this->logger->log()->warning("request log from IPAddress with ID", ["IPA" => $req->getIP(), "ID" => $req->getID()]);
    }
}
