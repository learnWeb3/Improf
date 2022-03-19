<?php

namespace Application\Controller;

use Psr\Http\Message\RequestInterface;
use Application\Core\Params;
use Application\Model\Schedule;

class SchedulesController extends ApplicationController
{
    public function __construct(
        RequestInterface $request,
        Params $params,
        string $action
    ) {
        parent::__construct($request, $params, $action);

        $this->requireParams([
            "show" => ["id"],
            "create" => ["id_formation", "start", "end", "total_capacity"],
            "update" => ["id"],
            "destroy" => ["id"],
        ]);

        $this->permitParams([
            "index" => ["id_formation", "start", "end", "total_capacity"],
            "create" => ["id_formation", "start", "end", "total_capacity"],
            "update" => ["id", "start", "end", "total_capacity"],
            "destroy" => ["id"],
        ]);
    }

    public function create()
    {
        $params = $this->params;
        $current_user = $this->current_user;
        $schedule = Schedule::register($params, $current_user);
        return $schedule;
    }
    public function update()
    {
        $params = $this->params;
        $current_user = $this->current_user;
        $schedule = Schedule::amend($params, $current_user);
        return $schedule;
    }
    public function destroy()
    {
        $params = $this->params;
        $current_user = $this->current_user;
        Schedule::remove($params, $current_user);
        $data =  json_encode(["message" => "schedule deleted with success"]);
        return $data;
    }
}
