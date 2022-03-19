<?php

namespace Application\Core\Middleware;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Application\Core\Params;

class QueryParamsParser implements IMiddleware
{
    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next)
    {
        $params = [];
        $query_string = $request->getUri()->getQuery();
        if (strlen($query_string) > 0) {
            $query_params = explode("&", $query_string);
            foreach ($query_params as $value) {
                $value = explode("=", $value);
                $params[$value[0]] = $value[1];
            }
        }
        $request->params = new Params($params);
        return $next($request, $response, $next);
    }
}
