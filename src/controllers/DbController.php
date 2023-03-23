<?php

namespace MvcFramework\Controllers;

use MvcFramework\Core\Controller;
use MvcFramework\Core\Method;
use MvcFramework\Core\Request;
use MvcFramework\Core\Response;
use MvcFramework\Services\DbConn;
use MvcFramework\Services\RedisConn;

class DbController extends Controller
{
    private DbConn $db;
    private RedisConn $redis;
    public function __construct(DbConn $db, RedisConn $redis)
    {
        $this->db = $db;
        $this->redis = $redis;
    }

    public function Index(Request $req, Response $res)
    {
        $res->json($this->db->open()->query("SELECT * from tab;"));
    }

    public function Find(Request $req, Response $res)
    {
        $req->registerMethods(Request::METHOD_GET, Request::METHOD_POST);
        $id = $req->getID();
        $res->json($this->db->open()->queryParam("SELECT * from test.tab WHERE id = ?;", "i", [$id]));
    }
}
