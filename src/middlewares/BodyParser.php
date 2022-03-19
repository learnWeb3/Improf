<?php

namespace Application\Core\Middleware;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

use Application\Core\Params;

class BodyParser implements IMiddleware
{

    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next)
    {
        if (in_array($request->getMethod(), ["POST", "PUT", "PATCH"])) {
            if (in_array("application/json", $request->getHeader("Content-Type"))) {
                $request->params = new Params(json_decode(file_get_contents("php://input"), true));
            } else if (in_array("application/x-www-form-urlencoded", $request->getHeader("Content-Type"))) {
                $request->params = new Params([...$_POST]);
            }
        }
        return $next($request, $response, $next);
    }
}
