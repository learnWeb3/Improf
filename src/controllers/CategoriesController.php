<?php

namespace Application\Controller;

use Psr\Http\Message\RequestInterface;
use Application\Core\Params;
use Application\Model\Category;

class CategoriesController extends ApplicationController
{
    public function __construct(
        RequestInterface $request,
        Params $params,
        string $action
    ) {
        parent::__construct($request, $params, $action);
        $this->requireParams([
            "show" => ["id"],
            "create" => ["label", "description"],
            "update" => ["id"],
            "destroy" => ["id"],
        ]);
        $this->permitParams([
            "show" => ["id"],
            "index" => ["label", "id"],
            "create" => ["label", "description"],
            "update" => ["id"],
            "destroy" => ["id"],
        ]);
    }

    public function index()
    {
        $params = $this->params;
        $categories = Category::getAll($params, $this->limit, $this->offset, $this->previous_page, $this->next_page);
        return $categories;
    }
    public function show()
    {
        $params = $this->params;
        $category = Category::getOne($params);
        return $category;
    }
    public function create()
    {
        $params = $this->params;
        $current_user = $this->current_user;
        $category = Category::register($params, $current_user);
        return $category;
    }
    public function update()
    {
        $params = $this->params;
        $current_user = $this->current_user;
        $category = Category::amend($params, $current_user);
        return $category;
    }

    public function destroy()
    {
        $params = $this->params;
        $current_user = $this->current_user;
        Category::remove($params, $current_user);
        $data = json_encode(["message" => "category deleted with success"]);
        return $data;
    }
}
