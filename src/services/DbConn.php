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

    /**
     * ping the db server
     * @return bool 
     */
    private function isAlive()
    {
        return $this->dbConn != null ? (bool)$this->dbConn->ping() : false;
    }

    /**
     * get the param type
     * @param mixed $param 
     * @return string `i` for integer, `s` for string and `d` for float/double
     */
    private static function paramType(mixed $param)
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

    /**
     * like `paramType` but with an array
     * @param array $params 
     * @return string all datatypes in one string in order of params array index
     */
    private static function getParamsType(array $params)
    {
        $ret = "";
        foreach ($params as $val) {
            $ret .= self::paramType($val);
        }
        return $ret;
    }

    /**
     * open the db connection
     * @return bool 
     * @throws ServiceException 
     */
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

    /**
     * close the db connection
     * @return void 
     */
    public function close()
    {
        if ($this->isAlive())
        {
            $this->dbConn->close();
        }
    }

    /**
     * dispose the db connection
     * @return void 
     */
    public function dispose()
    {
        $this->close();
        $this->dbConn = null;
    }

    /**
     * exec a non return dataset query with optional params for prepared queries
     * @param string $sql the sql stirng
     * @param null|array $params the params
     * @return QueryResult query result
     */
    public function exec(string $sql, ?array $params = null)
    {
        if (!$this->isAlive())
        {
            return new QueryResult(false);
        }
        $stmt = $this->dbConn->prepare($sql);
        if ($params != null && count($params) > 0)
        {
            $stmt->bind_param(self::getParamsType($params), ...$params);
        }
        if (!$stmt->execute())
        {
            return
            new QueryResult(false);
        }
        $queryResult = new QueryResult(true, $stmt->affected_rows, $stmt->insert_id);
        $stmt->close();
        return $queryResult;
    }

    /**
     * perform a query with dataset return in key=>value array
     * @param string $sql the sql string
     * @param null|array $params the params
     * @return false|array false on error, a double depth array on success (first nueric index for rows, second string index for cols)
     */
    public function query(string $sql, ?array $params = null)
    {
        if (!$this->isAlive())
        {
            return false;
        }
        $stmt = $this->dbConn->prepare($sql);
        if ($params != null && count($params) > 0)
        {
            $stmt->bind_param(self::getParamsType($params), ...$params);
        }
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

class QueryResult
{
    public bool $status;
    public int $affected_rows;
    public int $insert_id;

    public function __construct(bool $status, int $affected_rows = -1, int $insert_id = -1)
    {
        $this->status = $status;
        $this->affected_rows = $affected_rows;
        $this->insert_id;
    }

    public function setResult(bool $status, int $affected_rows = -1, int $insert_id = -1)
    {
        $this->status = $status;
        $this->affected_rows = $affected_rows;
        $this->insert_id;
        return $this;
    }
}
