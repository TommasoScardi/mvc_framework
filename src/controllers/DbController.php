<?php

namespace MvcFramework\Controllers;

use MvcFramework\Core\Controller;
use MvcFramework\Core\Request;
use MvcFramework\Core\Response;
use MvcFramework\Services\DbConn;

class DbController extends Controller
{
    private readonly DbConn $db;
    public function __construct(DbConn $db)
    {
        $this->db = $db;
    }

    public function Index(Request $req, Response $res)
    {
        $res->json($this->db->Query("SELECT * from tab;"));
    }

    public function Find(Request $req, Response $res)
    {
        $id = $req->getID();
        $res->json($this->db->QueryParam("SELECT * from test.tab WHERE id = ?;", "i", [$id]));
    }
}