<?php

namespace MvcFramework\Services;

use mysqli;

class DbConn
{
    private mysqli|null $conn;

    public function __construct(string $host, string $username, string $pwd, string $db, int $port = 3306)
    { $this->Open($host, $username, $pwd, $db, $port); }

    private function isAlive()
    {
        return $this->conn != null ? (bool)$this->conn->ping() : false;
    }

    public function Open(string $host, string $username, string $pwd, string $db, int $port = 3306)
    {
        $this->conn = new mysqli($host, $username, $pwd, $db, $port);

        if ($this->conn->connect_errno) {
            throw new DbExc("connection faliled ErrorNum =>" . $this->conn->connect_errno, DbExc::CODE_CONN_ERROR);
        }
        return (bool)$this->conn->ping();
    }

    public function Close()
    {
        if ($this->conn->ping()) {
            $this->conn->close();
        }
        $this->conn = null;
    }

    public function Exec(string $sql)
    {
        if ($this->isAlive())
        {
            if ($this->conn->query($sql))
            {
                return true;
            }
            if ($this->conn->errno)
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

    public function ExecParam(string $sql, string $paramsType, mixed ...$params)
    {
        if ($this->isAlive()) {
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param($paramsType, $params);
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

    public function Query(string $sql)
    {
        if ($this->isAlive()) {
            $res = $this->conn->query($sql);
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

    public function QueryParam(string $sql, string $paramsType, mixed ...$params)
    {
        if ($this->isAlive()) {
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param($paramsType, $params);
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
