<?php

namespace Application\Error;

class RouterError extends ApplicationError
{
    public function __construct($detailed_errors = [], $message = "Router error")
    {
        parent::__construct($detailed_errors, $message);
    }
}
