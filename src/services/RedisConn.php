<?php

namespace MvcFramework\Services;

class RedisConn {
    private Predis\Client $conn;

    private function isAlive()
    {
        return $this->conn != null ? (bool)$this->conn->ping() : false; 
    }

    public function Open(string $host, string $pwd = null, string $username = null, int $port = 6379)
    {
        if ($host == null || $host == "")
        {
            throw new DbExc("No hostname provided when connecting to redis", DbExc::CODE_CONN_ERROR);
        }
        $connOpt = array("scheme" => "tcp", "host" => $host, "port" => $port);
        if ($pwd != null && $pwd != "")
        {
            $connOpt["password"] = $pwd;
        }
        if ($username != null && $username != "" && array_key_exists("password", $connOpt))
        {
            $connOpt["username"] =$username;
        }

        $this->conn = new Predis\Client($connOpt);

        return (bool)$this->conn->ping();
    }

    public function Close()
    {
        if ($this->conn->ping())
        {
            $this->conn->close();
        }
        $this->conn = null;
    }

    public function Get(string $key)
    {
        if ($this->isAlive()) {
            $res = $this->conn->get($key);
            if (!$res) {
                return false;
            }
            return $res;
        } else {
            throw new DbExc(DbExc::STR_CONN_CLOSED, DbExc::CODE_CONN_CLOSED);
        }
    }

    public function Set(string $key, string $value, int $expSecTime = 0)
    {
        if ($this->isAlive())
        {
            $this->conn->set($key, $value);
            if ($expSecTime > 0)
            {
                $this->conn->expire($key, $expSecTime);
            }
        }
        else {
            throw new DbExc(DbExc::STR_CONN_CLOSED, DbExc::CODE_CONN_CLOSED);
        }
    }

    public function Delete(string $key)
    {
        if ($this->isAlive()) {
            return (bool)$this->conn->del($key);
        } else {
            throw new DbExc(DbExc::STR_CONN_CLOSED, DbExc::CODE_CONN_CLOSED);
        }
    }

    public function RenewExpiration(string $key, int $expSecTime)
    {
        if ($this->isAlive()) {
            return (bool)$this->conn->expire($key, $expSecTime);
        } else {
            throw new DbExc(DbExc::STR_CONN_CLOSED, DbExc::CODE_CONN_CLOSED);
        }
    }

    public function HGet(string $key, string $field)
    {
        if ($this->isAlive()) {
            $res = $this->conn->hget($key, $field);
            if (!$res) {
                return false;
            }
            return $res;
        } else {
            throw new DbExc(DbExc::STR_CONN_CLOSED, DbExc::CODE_CONN_CLOSED);
        }
    }

    public function HSet(string $key, string $field, string $value, int $expSecTime = 0)
    {
        if ($this->isAlive()) {
            $this->conn->hset($key, $field, $value);
            if ($expSecTime > 0) {
                $this->conn->expire($key, $expSecTime);
            }
        } else {
            throw new DbExc(DbExc::STR_CONN_CLOSED, DbExc::CODE_CONN_CLOSED);
        }
    }

    public function HDel(string $key)
    {
        if ($this->isAlive()) {
            return $this->conn->hdel($key);
        } else {
            throw new DbExc(DbExc::STR_CONN_CLOSED, DbExc::CODE_CONN_CLOSED);
        }
    }
}