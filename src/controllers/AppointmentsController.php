<?php

namespace Application\Controller;

use Psr\Http\Message\RequestInterface;
use Application\Core\Params;
use Application\Model\Appointment;

class AppointmentsController extends ApplicationController
{
    public function __construct(
        RequestInterface $request,
        Params $params,
        string $action
    ) {
        parent::__construct($request, $params, $action);

        $this->requireParams([
            "create" => ["id_schedule", "content"],
            "destroy" => ["id"],
        ]);
        $this->permitParams([
            "create" => ["id_schedule", "content"],
            "destroy" => ["id"],
        ]);
    }

    public function create()
    {
        $params = $this->params;
        $current_user = $this->current_user;
        $appointment = Appointment::register($params, $current_user);
        return $appointment;
    }

    public function destroy()
    {
        $params = $this->params;
        $current_user = $this->current_user;
        Appointment::remove($params, $current_user);
        $data = json_encode(["message" => "appointment deleted with success"]);
        return $data;
    }
}
