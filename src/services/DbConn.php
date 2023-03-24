<?php

namespace MvcFramework\Services;

use mysqli;
use MvcFramework\Core\Service;
use MvcFramework\Core\Exceptions\ServiceException;
use mysqli_sql_exception;

class DbConn implements Service
{
    private string $host;
    private string $username;
    private string $pwd;
    private string $dbName;
    private int $port = 3306;

    private ?mysqli $dbConn = null;

    /**
     * Set the internal auth data, needs to connect manually with open()
     * @param string $host 
     * @param string $username 
     * @param string $pwd 
     * @param string $dbName 
     * @param int $port 
     * @return void 
     */
    public function __construct(string $host, string $username, string $pwd, string $dbName, int $port = 3306)
    {
        $this->host = $host;
        $this->username = $username;
        $this->pwd = $pwd;
        $this->dbName = $dbName;
        $this->port = $port;
    }

    public function init()
    {
        return $this->open();
    }

    private function isAlive()
    {
        return $this->dbConn != null ? (bool)$this->dbConn->ping() : false;
    }

    private static function paramType($param)
    {
        switch (gettype($param))
        {
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
        try
        {
            if ($this->dbConn == null)
            {
                $this->dbConn = new mysqli($this->host, $this->username, $this->pwd, $this->dbName, $this->port);
                $this->dbConn->options(MYSQLI_OPT_INT_AND_FLOAT_NATIVE, 1);
                return true;
            }
            else
            {
                $this->dbConn->connect();
                return true;
            }
        }
        catch (mysqli_sql_exception $sqlExc)
        {
            throw new ServiceException($sqlExc->getMessage(), self::class, $sqlExc->getCode(), $sqlExc->getPrevious());
        }
    }

    public function close()
    {
        if ($this->isAlive())
        {
            $this->dbConn->close();
        }
    }

    public function dispose()
    {
        $this->close();
        $this->dbConn = null;
    }

    public function exec(string $sql)
    {
        if (!$this->isAlive())
        {
            return false;
        }
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

    public function execParam(string $sql, string $paramsType, array $params)
    {

        if (!$this->isAlive())
        {
            return false;
        }
        $stmt = $this->dbConn->prepare($sql);
        $stmt->bind_param($paramsType, ...$params);
        if (!$stmt->execute())
        {
            return false;
        }
        $rowsAff = $stmt->affected_rows;
        $stmt->close();
        return (bool)$rowsAff;
    }

    public function query(string $sql)
    {
        if (!$this->isAlive())
        {
            return false;
        }
        $res = $this->dbConn->query($sql);
        $resultSet = $res->fetch_all(MYSQLI_ASSOC);
        if (!$resultSet || $res->num_rows == 0)
        {
            return false;
        }
        return $resultSet;
    }

    public function queryParam(string $sql, string $paramsType, array $params)
    {
        if (!$this->isAlive())
        {
            return false;
        }
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
    }
}
