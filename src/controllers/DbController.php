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
        $cache = $this->redis->open()->get("home_find_$id");
        if (!$cache)
        {
            $data = $this->db->open()->queryParam("SELECT * from test.tab WHERE id = ?;", "i", [$id]);
            $this->redis->set("home_find_$id", json_encode($data), 10);
            $res->write("db")->end(json_encode($data));
            $res->json($data);
        }
        else
        {
            $res->write("cache")->end($cache);
            $this->redis->renewExpiration("home_find_$id", 10);
        }
    }
}
