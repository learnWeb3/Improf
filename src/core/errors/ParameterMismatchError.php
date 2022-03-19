<?php

namespace Application\Error;

class ParameterMismatchError extends ApplicationError
{
    public function __construct($detailed_errors = [], $message = "Parameter mismatch error")
    {
        parent::__construct($detailed_errors, $message);
    }
}
