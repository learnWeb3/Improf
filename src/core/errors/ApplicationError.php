<?php

namespace Application\Error;

class ApplicationError extends \Error
{
    private array $detailed_errors;
    public function __construct($detailed_errors = [], $message = "Database error")
    {
        parent::__construct($message);
        $this->detailed_errors = $detailed_errors;
    }

    public function __toString()
    {
        return json_encode([
            "message" => $this->getMessage(),
            "details" => $this->getDetailed_errors()
        ]);
    }

    /**
     * Get the value of detailed_errors
     */
    public function getDetailed_errors()
    {
        return $this->detailed_errors;
    }
}
