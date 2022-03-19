<?php

namespace Application\Core;

use Application\Error\ForbiddenError;

use Firebase\JWT\JWT;

class JWTWrapper
{
    private const JWT_SECRET = JWT_SECRET;
    private const JWT_ISS = JWT_ISS;

    private string $sub;
    private int $exp;
    private string $iss;

    public function __construct(string $sub, int $exp, string $iss)
    {
        $this->sub = $sub;
        $this->exp = $exp;
        $this->iss = $iss;
    }

    public static function decode(string $jwt_token): self
    {
        try {
            $jwt_token_vars = (array) JWT::decode($jwt_token, JWT_SECRET,  array_keys(JWT::$supported_algs));
            return new JWTWrapper($jwt_token_vars['sub'], $jwt_token_vars['exp'], $jwt_token_vars['iss']);
        } catch (\Firebase\JWT\ExpiredException) {
            throw new ForbiddenError([
                "token expired"
            ]);
        } catch (\Throwable $th) {
            throw new ForbiddenError([
                "invalid token"
            ]);
        }
    }

    public function verifyIssClaim()
    {
        if ($this->getIss() !== self::JWT_ISS) {
            throw new ForbiddenError([
                "invalid token"
            ]);
        }
    }

    /**
     * Get the value of sub
     */
    public function getSub()
    {
        return $this->sub;
    }

    /**
     * Set the value of sub
     *
     * @return  self
     */
    public function setSub($sub)
    {
        $this->sub = $sub;

        return $this;
    }

    /**
     * Get the value of exp
     */
    public function getExp()
    {
        return $this->exp;
    }

    /**
     * Set the value of exp
     *
     * @return  self
     */
    public function setExp($exp)
    {
        $this->exp = $exp;

        return $this;
    }

    /**
     * Get the value of iss
     */
    public function getIss()
    {
        return $this->iss;
    }

    /**
     * Set the value of iss
     *
     * @return  self
     */
    public function setIss($iss)
    {
        $this->iss = $iss;

        return $this;
    }

    public function __toString()
    {
        return JWT::encode(['sub' => $this->sub, "exp" => $this->exp, 'iss' => $this->iss], self::JWT_SECRET);
    }
}
