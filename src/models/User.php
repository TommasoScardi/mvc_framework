<?php

namespace MvcFramework\Models;

define("USER_TABLE", "users");
define("USER_FIELD_ID", "id");
define("USER_FIELD_NAME", "username");
define("USER_FIELD_PASSWORD", "password");
define("USER_FIELD_EMAIL", "email");
define("USER_FIELD_ISADMIN", "is_admin");

use Exception;
use MvcFramework\Core\MethodReturn;
use MvcFramework\Core\Model;
use MvcFramework\Services\DbConn;

class User extends Model
{
    private int $id;
    private string $name;
    private string $email;
    private bool $isAdmin;

    public function __construct(int $id = 0, string $name, string $email, bool $isAdmin)
    {
        $this->id = $id;
        $this->name = $name;
        if (!filter_var($email, FILTER_VALIDATE_EMAIL))
        {
            throw new Exception("email was in an incorrect format");
        }
        $this->email = $email;
        $this->isAdmin = $isAdmin;
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

    /**
     * Get the value of isAdmin
     */
    public function isAdmin()
    {
        return $this->isAdmin;
    }

    public static function find(DbConn $db, int|string $mailID)
    {
        $searchField = "";
        if (is_numeric($mailID))
        {
            $searchField = USER_FIELD_ID;
        }
        else
        {
            $searchField = USER_FIELD_EMAIL;
        }
        $userData = $db->query(sprintf("SELECT * from %s where %s = ?", USER_TABLE, $searchField), [$mailID]);
        if ($userData == null || count($userData) == 0)
        {
            return null;
        }
        return new User($userData[USER_FIELD_ID], $userData[USER_FIELD_NAME], $userData[USER_FIELD_EMAIL], $userData[USER_FIELD_ISADMIN]);
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
