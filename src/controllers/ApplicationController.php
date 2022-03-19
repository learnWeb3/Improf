<?php

namespace Application\Controller;

use Psr\Http\Message\RequestInterface;

use Application\Core\Params;
use Application\Error\ForbiddenParameterError;
use Application\Error\ParameterMismatchError;
use Application\Model\User;

class ApplicationController
{
    protected int $offset;
    protected int $limit;
    protected string $next_page;
    protected string $previous_page;
    protected User|null $current_user;
    protected Params $params;
    protected string $action;

    public function __construct(
        RequestInterface $request,
        Params $params,
        string $action
    ) {
        $this->request = $request;
        $this->action = $action;
        $this->setLimit();
        $this->setOffset();
        $this->setParams($params);
        $this->setCurrentUser($request);
        $this->setNextPage();
        $this->setPreviousPage();
    }

    protected function permitParams(array $permitted_keys): void
    {
        $current_action = $this->action;
        $params = get_object_vars($this->params);
        $forbidden_params = [];
        if (isset($permitted_keys[$current_action])) {
            foreach ($params as $key => $value) {
                if (!in_array($key, $permitted_keys[$current_action])) {
                    $forbidden_params[] = $key;
                }
            }
            if (count($forbidden_params) > 0) {
                throw new ForbiddenParameterError([
                    "forbidden_parameters" => $forbidden_params
                ]);
            }
        }
    }

    protected function permitParamsAsAdmin(array $permitted_keys): void
    {
        $this->permitParams($permitted_keys);
    }

    protected function requireParams(array $required_keys): void
    {
        $current_action = $this->action;
        $params = get_object_vars($this->params);
        $current_required_keys = [];
        if (isset($required_keys[$current_action])) {
            foreach ($params as $key => $value) {
                if (in_array($key, $required_keys[$current_action])) {
                    $current_required_keys[] = $key;
                }
            }
            if (count($required_keys[$current_action]) !== count($current_required_keys)) {
                throw new ParameterMismatchError([
                    "required_parameters" => $required_keys[$current_action],
                    "current_parameters" => $current_required_keys
                ]);
            }
        }
    }

    private function setLimit(): void
    {
        $params = $this->request->params;
        if (isset($params->limit)) {
            $limit = $params->limit;
        } else {
            $limit = PAGINATION_DEFAULT_LIMIT;
        }
        $this->limit = $limit;
    }

    private function setOffset(): void
    {
        $params = $this->request->params;
        if (isset($params->offset)) {
            $offset = $params->offset;
        } else {
            $offset = PAGINATION_DEFAULT_OFFSET;
        }
        $this->offset = $offset;
    }

    private function setNextPage(): void
    {
        $limit = $this->limit;
        $offset = $this->offset + PAGINATION_DEFAULT_LIMIT;
        $params_with_pagination = array_merge(get_object_vars($this->params), ["limit" => $limit, "offset" => $offset]);
        $final_params = [];
        foreach ($params_with_pagination as $key => $value) {
            $final_params[] = "$key=$value";
        };
        $final_params = implode("&", $final_params);
        $this->next_page = $_SERVER["PATH_INFO"] . "?" . $final_params;
    }

    private function setPreviousPage(): void
    {
        $limit = $this->limit;
        $offset = $this->offset - PAGINATION_DEFAULT_LIMIT > 0 ? $this->offset - PAGINATION_DEFAULT_LIMIT : 0;
        $params_with_pagination = array_merge(get_object_vars($this->params), ["limit" => $limit, "offset" => $offset]);
        $final_params = [];
        foreach ($params_with_pagination as $key => $value) {
            $final_params[] = "$key=$value";
        };
        $final_params = implode("&", $final_params);
        $this->previous_page = $_SERVER["PATH_INFO"] . "?" . $final_params;
    }

    private function setParams(Params $params): void
    {
        $params = clone $params;
        $params = $params->unset("offset")
            ->unset("limit");
        $this->params = $params;
    }

    private function setCurrentUser(RequestInterface $request): void
    {
        if (isset($request->jwt_token)) {
            $jwt_token = $request->jwt_token;
            $current_user = User::getUserFromToken($jwt_token);
        } else {
            $current_user = null;
        }
        $this->current_user = $current_user;
    }
}
