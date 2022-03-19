<?php

namespace Application\Controller;

use Psr\Http\Message\RequestInterface;
use Application\Core\Params;
use Application\Model\User;
use Application\Core\JWTWrapper;


class SessionsController extends ApplicationController
{
    public function __construct(
        RequestInterface $request,
        Params $params,
        string $action
    ) {
        parent::__construct($request, $params, $action);
        $this->requireParams([
            "login" => ["password", "email"]
        ]);
        $this->permitParams([
            "login" => ["password", "email"]
        ]);
    }

    public function login()
    {
        $params = $this->params;
        $user = User::authenticate($params);

        $sub = $user->id;
        $exp = time() + JWT_EXP_TIME;
        $iss = JWT_ISS;

        $jwt_token = (string) new JWTWrapper($sub,  $exp, $iss);

        $data = json_encode([
            "token" => $jwt_token
        ]);

        return $data;
    }
}
