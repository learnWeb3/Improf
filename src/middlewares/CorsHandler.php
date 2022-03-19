<?php

namespace Application\Core\Middleware;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class CorsHandler implements IMiddleware
{
    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next)
    {
        $response = $response->withAddedHeader('Access-Control-Allow-Origin', '*');
        $response = $response->withAddedHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE');
        $response = $response->withAddedHeader('Access-Control-Allow-Credentials', 'true');
        $response = $response->withAddedHeader('Access-Control-Allow-Headers', '*');
        return $next($request, $response, $next);
    }
}
