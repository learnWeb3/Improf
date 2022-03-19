<?php

namespace Application\Error;

class DatabaseError extends ApplicationError
{
    public function __construct($detailed_errors = [], $message = "Database error", )
    {
        parent::__construct($detailed_errors, $message);
    }
}
