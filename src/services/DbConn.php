<?php

namespace MvcFramework\Services;

use mysqli;
use MvcFramework\Exceptions\DbExc;

class DbConn
{
    private string $host;
    private string $username;
    private string $pwd;
    private string $dbName;
    private int $port = 3306;

    private ?mysqli $dbConn = null;

    public function __construct(string $host, string $username, string $pwd, string $dbName, int $port = 3306)
    {
        $this->host = $host;
        $this->username = $username;
        $this->pwd = $pwd;
        $this->dbName = $dbName;
        $this->port = $port;
    }

    private function isAlive()
    {
        return $this->dbConn != null ? (bool)$this->dbConn->ping() : false;
    }

    private static function paramType($param)
    {
        switch (gettype($param)) {
            case "integer":
                return 'i';
            case "double":
                return 'd';
            default:
                return 's';
        }
    }

    public function open()
    {
        if ($this->dbConn == null) {
            $this->dbConn = new mysqli($this->host, $this->username, $this->pwd, $this->dbName, $this->port);
            $this->dbConn->options(MYSQLI_OPT_INT_AND_FLOAT_NATIVE, 1);
        } else {
            $this->dbConn->connect();
        }

        if ($this->dbConn->connect_errno) {
            throw new DbExc("connection faliled ErrorNum =>" . $this->dbConn->connect_errno, DbExc::CODE_CONN_ERROR);
        }
        return $this;
    }

    public function close()
    {
        if ($this->isAlive()) {
            $this->dbConn->close();
        }
    }

    public function dispose() {
        $this->close();
        $this->dbConn = null;
    }

    public function exec(string $sql)
    {
        if ($this->isAlive())
        {
            if ($this->dbConn->query($sql))
            {
                return true;
            }
            if ($this->dbConn->errno)
            {
                return false;
            }
            return false;
        }
        else
        {
            throw new DbExc(DbExc::STR_CONN_CLOSED, DbExc::CODE_CONN_CLOSED);
        }
    }

    public function execParam(string $sql, string $paramsType, array $params)
    {
        if ($this->isAlive()) {
            $stmt = $this->dbConn->prepare($sql);
            $stmt->bind_param($paramsType, ...$params);
            if (!$stmt->execute())
            {
                return false;
            }
            $rowsAff = $stmt->affected_rows;
            $stmt->close();
            return (bool)$rowsAff;
        } else {
            throw new DbExc(DbExc::STR_CONN_CLOSED, DbExc::CODE_CONN_CLOSED);
        }
    }

    public function query(string $sql)
    {
        if ($this->isAlive()) {
            $res = $this->dbConn->query($sql);
            $resultSet = $res->fetch_all(MYSQLI_ASSOC);
            if(!$resultSet || $res->num_rows == 0)
            {
                return false;
            }
            return $resultSet;
        } else {
            throw new DbExc(DbExc::STR_CONN_CLOSED, DbExc::CODE_CONN_CLOSED);
        }
    }

    public function queryParam(string $sql, string $paramsType, array $params)
    {
        if ($this->isAlive()) {
            $stmt = $this->dbConn->prepare($sql);
            $stmt->bind_param($paramsType, ...$params);
            if (!$stmt->execute())
            {
                return false;
            }
            $res = $stmt->get_result();
            if ($res->num_rows == 0)
            {
                return false;
            }
            $resultSet = $res->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            return $resultSet;
        } else {
            throw new DbExc(DbExc::STR_CONN_CLOSED, DbExc::CODE_CONN_CLOSED);
        }
    }
}
