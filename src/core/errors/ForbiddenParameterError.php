<?php

namespace Application\Error;

class ForbiddenParameterError extends ApplicationError
{
    public function __construct($detailed_errors = [], $message = "Forbidden parameter")
    {
        parent::__construct($detailed_errors, $message);
    }
}
