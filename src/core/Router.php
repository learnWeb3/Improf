<?php

namespace Application\Core;

use Application\Controller\ApplicationController;
use Application\Helper\StringUtils;

use Psr\Http\Message\RequestInterface;

class Router
{
    public static function route(RequestInterface $request, Params $params)
    {
        $target = explode("#", $request->target);

        $controller_name = $target[0];
        $action = $target[1];

        $controller_name = "Application\Controller\\".StringUtils::toControllerName($controller_name);
        $controller = new $controller_name($request, $params, $action);

        return $controller->$action();
    }
}
