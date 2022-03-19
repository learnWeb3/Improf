<?php

namespace Application\Error;

class ForbiddenError extends ApplicationError
{
    public function __construct( $detailed_errors = [], $message="Forbidden")
    {
        parent::__construct($detailed_errors, $message);
    }
}
