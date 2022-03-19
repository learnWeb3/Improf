<?php

namespace Application\Controller;

use Psr\Http\Message\RequestInterface;
use Application\Core\Params;

class StaticController extends ApplicationController
{
    public function __construct(
        RequestInterface $request,
        Params $params,
        string $action
    ) {
        parent::__construct($request, $params, $action);
    }

    public function home()
    {
        return json_encode([
            "message" => "Welcome to Improf: A collaborative platform where everyone gets to share it's knowledge"
        ]);
    }
}
