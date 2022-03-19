<?php

namespace Application\Core;

use Application\Core\Middleware\IMiddleware;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Dispatcher
{
    private array $middlewares;
    private int $index = -1;

    public function __construct()
    {
        $this->middlewares = [];
    }

    public function pipe(IMiddleware $middleware):void
    {
        $this->middlewares[] = $middleware;
    }

    public function process(RequestInterface $request, ResponseInterface $response):ResponseInterface
    {
        if ($this->index < count($this->middlewares) - 1) {
            $this->index++;
            $response = $this->middlewares[$this->index]($request, $response, [$this, 'process']);
            return $response;
        } else {
            return $response;
        }
    }
}
