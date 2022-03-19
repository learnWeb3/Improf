<?php

namespace Application\Controller;

use Psr\Http\Message\RequestInterface;

use Application\Core\Params;
use Application\Model\Meeting;

class MeetingsController extends ApplicationController
{
    public function __construct(
        RequestInterface $request,
        Params $params,
        string $action
    ) {
        parent::__construct($request, $params, $action);
        $this->requireParams([
            "create" => ["id_schedule", "password", "agenda"],
            "destroy" => ["id"],
        ]);
        $this->permitParams([
            "create" => ["id_schedule", "password", "agenda"],
            "destroy" => ["id", "access_token"],
        ]);
    }

    public function create()
    {
        $current_user = $this->current_user;
        $params = $this->params;

        $meeting = Meeting::register($params, $current_user);
        return $meeting;
    }

    public function destroy()
    {
        $current_user = $this->current_user;
        $params = $this->params;

        Meeting::remove($params, $current_user);
        
        $data = json_encode(["message" => "meeting deleted with success"]);
        return $data;
    }
}
