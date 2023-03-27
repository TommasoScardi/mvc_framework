<?php

namespace MvcFramework\Controllers;

use MvcFramework\Core\Application;
use MvcFramework\Core\Controller;
use MvcFramework\Core\Request;
use MvcFramework\Core\Response;
use MvcFramework\Models\User;
use MvcFramework\Services\AppLogger;
use MvcFramework\Services\DbConn;

class AccountController extends Controller
{
    private AppLogger $logger;
    private DbConn $db;

    public function __construct(AppLogger $logger, DbConn $db)
    {
        $this->logger = $logger;
        $this->db = $db;
    }

    public function Login(Request $req, Response $res)
    {
        $req->registerMethods(Request::METHOD_POST);
        $data = $req->getBody(["email", "pwd"]);
        $user = new User(0,"", $data["email"]);
        $dbData = User::login($this->db, $user, $data["pwd"]);
        // password_verify($data["pwd"])
        if ($dbData->status)
        {
            $res->json($dbData->message);
        }
        else
        {
            $res->error(400, $dbData->message);
        }
    }

}
