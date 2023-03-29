<?php

namespace MvcFramework\Models;

define("USER_TABLE", "users");
define("USER_FIELD_NAME", "username");
define("USER_FIELD_PASSWORD", "password");
define("USER_FIELD_EMAIL", "email");

use Exception;
use MvcFramework\Core\MethodReturn;
use MvcFramework\Core\Model;
use MvcFramework\Services\DbConn;

class User extends Model
{
    private int $id;
    private string $name;
    private string $email;

    public function __construct(int $id = 0, string $name, string $email)
    {
        $this->id = $id;
        $this->name = $name;
        if (!filter_var($email, FILTER_VALIDATE_EMAIL))
        {
            throw new Exception("email was in an incorrect format");
        }
        $this->email = $email;
    }

    /**
     * Get the value of id
     */ 
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the value of name
     */ 
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the value of email
     */ 
    public function getEmail()
    {
        return $this->email;
    }

    public static function login(DbConn $db, $email, string $password)
    {
        $ret = new MethodReturn();
        if (!filter_var($email, FILTER_VALIDATE_EMAIL))
        {
            return $ret->setReturn(false, "invalid email");
        }
        $userData = $db->query(sprintf("SELECT * from %s where %s = ?;", USER_TABLE, USER_FIELD_EMAIL), [$email]);
        if (!$userData)
        {
            return $ret->setReturn(false, "no user found with email gived");
        }
        else if (count($userData) > 1)
        {
            return $ret->setReturn(false, "email ambiguous");
        }
        
        if(!password_verify($password, $userData[0]["password"]))
        {
            return $ret->setReturn(false, "wrong password");
        }

        return $ret->setReturn(true, "login success");
    }

    public function register(DbConn $db, string $password)
    {
        $ret = new MethodReturn();

        $pwdHash = password_hash($password, PASSWORD_DEFAULT);
        if (!$pwdHash)
        {
            return $ret->setReturn(false, "unable to hash password");
        }

        $queryRes = $db->exec(sprintf("INSERT into %s (%s, %s, %s) value (?, ?, ?);", USER_TABLE, USER_FIELD_NAME, USER_FIELD_EMAIL, USER_FIELD_PASSWORD), [$this->name, $this->email, $pwdHash]);
        if (!$queryRes)
        {
            return $ret->setReturn(false, "query error");
        }
        
    }
}
