<?php

namespace Application\Controller;

use Psr\Http\Message\RequestInterface;
use Application\Core\Params;
use Application\Model\Formation;

class FormationsController extends ApplicationController
{
    public function __construct(
        RequestInterface $request,
        Params $params,
        string $action
    ) {
        parent::__construct($request, $params, $action);
        $this->requireParams([
            "show" => ["id"],
            "create" => ["label", "description", "id_category"],
            "update" => ["id"],
            "destroy" => ["id"],
        ]);
        $this->permitParams([
            "show" => ["id"],
            "index" => ["label", "id", "id_category", "id_user"],
            "create" => ["label", "description", "id_category"],
            "update" => ["id", "label", "description"],
            "destroy" => ["id"],
        ]);
    }

    public function index()
    {
        $params = $this->params;
        $formations = Formation::getAll($params, $this->limit, $this->offset, $this->previous_page, $this->next_page);
        return $formations;
    }
    public function show()
    {
        $params = $this->params;
        $current_user = $this->current_user;
        $formation = Formation::findOne($params, $current_user);
        return $formation;
    }
    public function create()
    {
        $params = $this->params;
        $current_user = $this->current_user;
        $formation = Formation::register($params, $current_user);
        return $formation;
    }
    public function update()
    {
        $params = $this->params;
        $current_user = $this->current_user;
        $formation = Formation::amend($params, $current_user);
        return $formation;
    }
    public function destroy()
    {
        $params = $this->params;
        $current_user = $this->current_user;
        Formation::remove($params, $current_user);
        $data =  json_encode(["message" => "formation deleted with success"]);
        return $data;
    }
}
