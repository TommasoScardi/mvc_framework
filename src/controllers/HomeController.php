<?php

namespace MvcFramework\Controllers;

use MvcFramework\Core\Application;
use MvcFramework\Core\Controller;
use MvcFramework\Core\Request;
use MvcFramework\Core\Response;
use MvcFramework\Services\AppLogger;

class HomeController extends Controller
{
    private AppLogger $logger;
    public function __construct(AppLogger $logger)
    {
        $this->logger = $logger;
    }

    public function Index(Request $req, Response $res)
    {
        $res->end("hello world");
    }

    public function Guid(Request $req, Response $res)
    {
        $res->end(Application::getGUID());
    }

    public function Log(Request $req, Response $res)
    {
        $res->end("logging to file " . $req->getIP() . " id => " . $req->getID());
        $this->logger->log()->warning("request log from IPAddress with ID", ["IPA" => $req->getIP(), "ID" => $req->getID()]);
    }

    public function Download(Request $req, Response $res)
    {
        $res->downloadFile("templates/email/empty.html", "empty.html");
    }
}
