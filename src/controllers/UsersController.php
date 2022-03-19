<?php

namespace Application\Controller;

use Psr\Http\Message\RequestInterface;
use Application\Core\Params;
use Application\Error\DatabaseError;
use Application\Model\User;


class UsersController extends ApplicationController
{
    public function __construct(
        RequestInterface $request,
        Params $params,
        string $action
    ) {
        parent::__construct($request, $params, $action);

        $this->requireParams([
            "show" => ["id"],
            "create" => ["username", "email", "password", "description"],
            "update" => ["id"],
            "destroy" => ["id"]
        ]);

        $this->permitParams([
            "show" => ["id"],
            "index" => ["username", "email", "id"],
            "create" => ["username", "email", "password", "description"],
            "update" => ["id", "username", "email", "password", "description"],
            "destroy" => ["id"],
        ]);

        $this->permitParamsAsAdmin([
            "update" => ["id", "username", "email", "password", "description", "role"],
        ]);
    }

    public function index()
    {
        $params = $this->params;
        $users = User::getAll($params, $this->limit, $this->offset, $this->previous_page, $this->next_page);
        return $users;
    }
    public function show()
    {
        $params = $this->params;
        $current_user = $this->current_user;
        $user = User::findOne($params,$current_user);
        return $user;
    }
    public function create()
    {
        $params = $this->params;
        $user = User::register($params);
        return $user;
    }
    public function update()
    {
        $params = $this->params;
        $current_user = $this->current_user;
        $user = User::amend($params, $current_user);
        return $user;
    }
    public function destroy()
    {
        $params = $this->params;
        $current_user = $this->current_user;
        User::remove($params, $current_user);
        $data =  json_encode(["message" => "user deleted with success"]);
        return $data;
    }
}
