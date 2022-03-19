<?php

namespace Application\Controller;

use Psr\Http\Message\RequestInterface;
use Application\Core\Params;
use Application\Model\Review;

class ReviewsController extends ApplicationController
{
    public function __construct(
        RequestInterface $request,
        Params $params,
        string $action
    ) {
        parent::__construct($request, $params, $action);
        $this->requireParams([
            "show" => ["id"],
            "create" => ["id_appointment", "content", "rate"],
            "update" => ["id"],
            "destroy" => ["id"],
        ]);
        $this->permitParams([
            "show" => ["id"],
            "index" => ["id_appointment", "id", "rate"],
            "create" => ["id_appointment", "content", "rate"],
            "update" => ["id", "content", "rate"],
            "destroy" => ["id"],
        ]);
    }

    public function create()
    {
        $current_user = $this->current_user;
        $params = $this->params;
        $review = Review::register($params, $current_user);
        return $review;
    }
    public function update()
    {
        $params = $this->params;
        $current_user = $this->current_user;
        $review = Review::amend($params, $current_user);
        return $review;
    }
    public function destroy()
    {
        $params = $this->params;
        $current_user = $this->current_user;
        Review::remove($params, $current_user);
        $data =  json_encode(["message" => "review deleted with success"]);
        return $data;
    }
}
