<?php

namespace MvcFramework\Services;
use MvcFramework\Exceptions\DbExc;

class RedisConn {
    private string $host;
    private string $pwd;
    private string $username;
    private int $port;

    private ?Predis\Client $redisConn = null;

    public function __construct(string $host, string $pwd = null, string $username = null, int $port = 6379)
    {
        $this->host = $host;
        $this->username = $username;
        $this->pwd = $pwd;
        $this->port = $port;
    }

    private function isAlive()
    {
        return $this->redisConn != null ? (bool)$this->redisConn->ping() : false; 
    }

    public function open()
    {
        if ($this->host == null || $this->host == "")
        {
            throw new DbExc("No hostname provided when connecting to redis", DbExc::CODE_CONN_ERROR);
        }
        $connOpt = array("scheme" => "tcp", "host" => $this->host, "port" => $this->port);
        if ($this->pwd != null && $this->pwd != "")
        {
            $connOpt["password"] = $this->pwd;
        }
        if ($this->username != null && $this->username != "" && array_key_exists("password", $connOpt))
        {
            $connOpt["username"] = $this->username;
        }

        $this->redisConn = new Predis\Client($connOpt);

        return (bool)$this->redisConn->ping();
    }

    public function close()
    {
        if ($this->isAlive()) {
            $this->redisConn->close();
        }
    }

    public function dispose()
    {
        $this->close();
        $this->redisConn = null;
    }

    public function get(string $key)
    {
        if ($this->isAlive()) {
            $res = $this->redisConn->get($key);
            if (!$res) {
                return false;
            }
            return $res;
        } else {
            throw new DbExc(DbExc::STR_CONN_CLOSED, DbExc::CODE_CONN_CLOSED);
        }
    }

    public function set(string $key, string $value, int $expSecTime = 0)
    {
        if ($this->isAlive())
        {
            $this->redisConn->set($key, $value);
            if ($expSecTime > 0)
            {
                $this->redisConn->expire($key, $expSecTime);
            }
        }
        else {
            throw new DbExc(DbExc::STR_CONN_CLOSED, DbExc::CODE_CONN_CLOSED);
        }
    }

    public function delete(string $key)
    {
        if ($this->isAlive()) {
            return (bool)$this->redisConn->del($key);
        } else {
            throw new DbExc(DbExc::STR_CONN_CLOSED, DbExc::CODE_CONN_CLOSED);
        }
    }

    public function renewExpiration(string $key, int $expSecTime)
    {
        if ($this->isAlive()) {
            return (bool)$this->redisConn->expire($key, $expSecTime);
        } else {
            throw new DbExc(DbExc::STR_CONN_CLOSED, DbExc::CODE_CONN_CLOSED);
        }
    }

    public function hGet(string $key, string $field)
    {
        if ($this->isAlive()) {
            $res = $this->redisConn->hget($key, $field);
            if (!$res) {
                return false;
            }
            return $res;
        } else {
            throw new DbExc(DbExc::STR_CONN_CLOSED, DbExc::CODE_CONN_CLOSED);
        }
    }

    public function hSet(string $key, string $field, string $value, int $expSecTime = 0)
    {
        if ($this->isAlive()) {
            $this->redisConn->hset($key, $field, $value);
            if ($expSecTime > 0) {
                $this->redisConn->expire($key, $expSecTime);
            }
        } else {
            throw new DbExc(DbExc::STR_CONN_CLOSED, DbExc::CODE_CONN_CLOSED);
        }
    }

    public function hDel(string $key)
    {
        if ($this->isAlive()) {
            return $this->redisConn->hdel($key);
        } else {
            throw new DbExc(DbExc::STR_CONN_CLOSED, DbExc::CODE_CONN_CLOSED);
        }
    }
}