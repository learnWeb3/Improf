<?php

namespace Application\Controller;

use Psr\Http\Message\RequestInterface;
use Application\Core\Params;
use Application\Service\ZoomOAuth;

class ZoomsController extends ApplicationController
{
    public function __construct(
        RequestInterface $request,
        Params $params,
        string $action
    ) {
        parent::__construct($request, $params, $action);
    }

    public function callback()
    {
        $params = $this->params;
        $authorization_code = $params->code;
        $data = ZoomOAuth::requestAccessToken($authorization_code);
        return json_encode($data);
    }

    public function authorize()
    {
        $data = ZoomOAuth::requestAuthorizeUrl();
        return json_encode($data);
    }
}
