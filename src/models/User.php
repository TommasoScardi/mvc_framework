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

    public static function login(DbConn $db, User $user, string $password)
    {
        $ret = new MethodReturn();
        $userData = $db->queryParam(sprintf("SELECT * from %s where %s = ?;", USER_TABLE, USER_FIELD_EMAIL), [$user->getEmail()]);
        if (!$userData)
        {
            $ret->status = false;
            $ret->message = "no user found with email gived";
            return $ret;
        }
        else if (count($userData) > 1)
        {
            $ret->status = false;
            $ret->message = "email ambiguous";
            return $ret;
        }
        
        if(!password_verify($password, $userData[0]["password"]))
        {
            $ret->status = false;
            $ret->message = "wrong password";
            return $ret;
        }

        $ret->status = true;
        $ret->message = "login success";
        return $ret;
    }
}
