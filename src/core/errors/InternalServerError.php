<?php

namespace Application\Error;

class InternalServerError extends ApplicationError
{
    public function __construct( $detailed_errors = [], $message = "Internal server error")
    {
        parent::__construct($detailed_errors, $message);
    }
}
