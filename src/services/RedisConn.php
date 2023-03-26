<?php

namespace MvcFramework\Services;

use Predis\Client as RedisClient;

use MvcFramework\Core\Service;
use MvcFramework\Core\Exceptions\ServiceException;

class RedisConn implements Service
{
    private string $host;
    private ?string $pwd;
    private ?string $username;
    private int $port;

    private ?RedisClient $redisConn = null;

    /**
     * Set the internal auth data, needs to connect manually with open()
     * @param string $host the host where redis server is in execution
     * @param string|null $pwd the server password (if server is configured with ACL it needs an username the user password)
     * @param string|null $username needed if ACL is configured, the username of the pwd
     * @param int $port server port
     * @throws DbExc if no host is provided
     * @return void 
     */
    public function __construct(string $host, ?string $pwd = null, ?string $username = null, int $port = 6379)
    {
        $this->host = $host;
        $this->username = $username;
        $this->pwd = $pwd;
        $this->port = $port;
    }

    public function init()
    {
        return $this->open();
    }

    /**
     * Verify if connection is alive pinging the server
     * @return bool
     */
    private function isAlive()
    {
        return $this->redisConn != null ? (bool)$this->redisConn->ping() : false;
    }

    /**
     * Open the connection
     * @return bool
     */
    //FIXME if connection is not established no exc is throw and apache web restart itself
    public function open()
    {
        $connOpt = array("scheme" => "tcp", "host" => $this->host, "port" => $this->port);
        if ($this->pwd != null && $this->pwd != "")
        {
            $connOpt["password"] = $this->pwd;
        }
        if ($this->username != null && $this->username != "" && array_key_exists("password", $connOpt))
        {
            $connOpt["username"] = $this->username;
        }

        $this->redisConn = new RedisClient($connOpt);
        return true;
    }

    /**
     * Closes the connection to the server
     * @return void 
     */
    public function close()
    {
        if ($this->isAlive())
        {
            $this->redisConn->close();
        }
    }

    /**
     * Dispose the object closing the connection
     * @return void 
     */
    public function dispose()
    {
        $this->close();
        $this->redisConn = null;
    }

    /**
     * Get a value with a key provided
     * @param string $key the key string
     * @return false|string false if nothing is fond, the value otherwise
     * @throws DbExc if connection is closed
     */
    public function get(string $key)
    {
        if (!$this->isAlive())
        {
            return false;
        }
        $res = $this->redisConn->get($key);
        if (!$res)
        {
            return false;
        }
        return $res;
    }

    /**
     * Set a value with a gived key
     * @param string $key the key
     * @param string $value value
     * @param int $expSecTime expiration time in seconds
     * @return void 
     * @throws DbExc if connection is closed
     */
    public function set(string $key, string $value, int $expSecTime = 0)
    {
        if (!$this->isAlive())
        {
            return false;
        }
        $this->redisConn->set($key, $value);
        if ($expSecTime > 0)
        {
            $this->redisConn->expire($key, $expSecTime);
        }
    }

    /**
     * Delete a key from redis
     * @param string $key the key
     * @return bool true on success otherwise false
     * @throws DbExc if connection is closed
     */
    public function delete(string $key)
    {
        if (!$this->isAlive())
        {
            return false;
        }
        return (bool)$this->redisConn->del($key);
    }

    /**
     * Renews the TTL of a gived key
     * @param string $key the key
     * @param int $expSecTime the new exp time
     * @return bool true on success otherwise false
     * @throws DbExc if connection is closed
     */
    public function renewExpiration(string $key, int $expSecTime)
    {
        if (!$this->isAlive())
        {
            return false;
        }
        return (bool)$this->redisConn->expire($key, $expSecTime);
    }

    /**
     * Get a DATASET from the redis
     * @param string $key the master key
     * @param string $field the field key
     * @return false|string false if nothing is fond, the value otherwise
     * @throws DbExc if connection is closed
     */
    public function hGet(string $key, string $field)
    {
        if (!$this->isAlive())
        {
            return false;
        }
        $res = $this->redisConn->hget($key, $field);
        if (!$res)
        {
            return false;
        }
        return $res;
    }

    /**
     * Set a DATASET onto redis
     * @param string $key the master key
     * @param string $field the filed key
     * @param string $value the value
     * @param int $expSecTime the expiration TTL
     * @return void 
     * @throws DbExc if connection is closed
     */
    public function hSet(string $key, string $field, string $value, int $expSecTime = 0)
    {
        if (!$this->isAlive())
        {
            return false;
        }
        $this->redisConn->hset($key, $field, $value);
        if ($expSecTime > 0)
        {
            $this->redisConn->expire($key, $expSecTime);
        }
    }

    /**
     * Delee all the field in a DATASET
     * @param string $key the master key
     * @return int num of field deleted
     * @throws DbExc if connection is closed
     */
    public function hDel(string $key)
    {
        if (!$this->isAlive())
        {
            return false;
        }
        return $this->redisConn->hdel($key);
    }
}
