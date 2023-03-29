<?php

namespace MvcFramework\Services;

use DomainException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\BeforeValidException;
use MvcFramework\Core\Exceptions\ServiceException;
use MvcFramework\Core\Request;
use MvcFramework\Core\Service;
use MvcFramework\Models\User;
use UnexpectedValueException;

class JwtManager implements Service
{
    private static string $ENC_ALGO = "HS256";
    private static string $REDIS_KEY_PREFIX = "jwt:";
    
    private int $authTokenDuration;
    private string $authTokenIssuer;
    private string $authTokenCreationK;

    public function __construct(int $authTokenDuration,
        string $authTokenIssuer, string $authTokenCreationK)
    {
        $this->authTokenDuration = $authTokenDuration;
        $this->authTokenIssuer = $authTokenIssuer;
        $this->authTokenCreationK = $authTokenCreationK;
    }

    public function init() 
    {
        return true;
    }

    public function CreateToken(RedisConn $r, User $user)
    {
        $issuedAt = time();
        $expiration = $issuedAt + $this->authTokenDuration;

        $jwtPayload = array(
            //Issuer => chi genera, rilascia il token
            "iss" => $this->authTokenIssuer
            //Audience => stringa o array di stringhe contente/i ruoli (privilegi) di chi ha richiesto il TOKEN
            //NON USATO NEL MODO RICHIESTO
            , "aud" => $this->authTokenIssuer
            //IssuedAt => unix timestamp della creazione del token
            , "iat" => $issuedAt
            //Expiration => unix timestamp della scadenza
            , "exp" => $expiration
            , "user_uid" => $user->getId()
            , "is_admin" => $user->isAdmin()
        );

        $jwt = JWT::encode($jwtPayload, $this->authTokenCreationK, self::$ENC_ALGO);

        if (!$jwt)
        {
            return null;
        }
        else
        {
            //imposta un record redis con key la stringa del token jwt e il prefisso e con contenuto 1
            //1 significa che l'utente X è ancora integro durante sessione attuale; 0 no (un admin non gli ha revocato/concesso dei permessi, non è stato bannato)
            $r->set(self::$REDIS_KEY_PREFIX.$jwt, 1, $this->authTokenDuration);
            return array("token" => $jwt, "expires" => $expiration);
        }
    }

    public function Authorize(Request $req, RedisConn $r, bool $adminRequired = false)
    {
        $headerAuth = $req->getAuthHeader();
        if ($headerAuth == null)
        {
            throw new ServiceException("unauthorized, no token provided for the resource requested", self::class, 401, $req->getPath());
        }
        $explodedHeader = explode(" ", $headerAuth); //TODO
        if (!$explodedHeader || count($explodedHeader) < 2) //2 because a jwt token is in this format => Bearer <TOKEN>
        {
            throw new ServiceException("auth token provided is in a wrong format or is blank", self::class, 400, ["headerAuth" => $headerAuth]);
        }
        else if (strtolower($explodedHeader[0]) === "bearer")
        {
            throw new ServiceException("auth token provided is in a wrong format, Bearer keyword missing", self::class, 400, ["headerAuth" => $headerAuth]);
        }
        try
        {
            $jwt = $explodedHeader[1]; // index 0 is Bearer, index 1 is the token
            $jwtPayload = (array)JWT::decode($jwt, new Key($this->authTokenCreationK, self::$ENC_ALGO));

            $redisToken = $r->get(self::$REDIS_KEY_PREFIX . $jwt);
            if ($redisToken && is_numeric($redisToken) && intval($redisToken) === 1)
            {
                return $adminRequired ? $jwtPayload["is_admin"] : true;
            }
            else
            {
                http_response_code(401);
                echo json_encode((object)array("message" => "unauthorized, the token provided is not valid"));
                exit;
            }
        }
        catch (ExpiredException | BeforeValidException | DomainException | UnexpectedValueException $e)
        {
            http_response_code(401);
            echo json_encode((object)array("message" => "unauthorized, " . $e->getMessage()));
            exit;
        }
    }

    public function RenewToken()
    {
        //TODO
    }
}
