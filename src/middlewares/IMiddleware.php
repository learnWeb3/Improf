<?php

namespace Application\Core\Middleware;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface IMiddleware
{
    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next);
}
