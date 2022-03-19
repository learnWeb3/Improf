<?php

namespace Application\Core\Middleware;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;


class AuthHandler implements IMiddleware
{
    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next)
    {
        if (count($request->getHeader("Authorization")) > 0) {
            try {
                $jwt_token = explode(" ", $request->getHeader("Authorization")[0])[1];
                $request->jwt_token = $jwt_token;
                return $next($request, $response, $next);
            } catch (\Throwable $th) {
                $response->withStatus(500);
                $response->withAddedHeader("content-type", 'application/json');
                $data = ["message" => "Bearer Authorization header malformed"];
                $response->getBody()->write(json_encode($data));
                return $response;
            }
        }
        return $next($request, $response, $next);
    }
};
