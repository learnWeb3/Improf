<?php
namespace Application\Error;

class NotFoundError extends ApplicationError
{
    public function __construct($detailed_errors = [], $message = "Not found")
    {
        parent::__construct($detailed_errors, $message);
    }
}
