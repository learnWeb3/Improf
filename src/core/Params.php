<?php

namespace Application\Core;

class Params
{
    public function __construct($parameters = null)
    {
        if (is_null($parameters) === false) {
            foreach ($parameters as $key => $value) {
                $this->append($key, $value);
            }
        }
    }

    public function append(string $key, string|int|bool $value):self
    {
        $this->$key = $value;
        return $this;
    }

    public function unset(string $key):self
    {
        unset($this->$key);
        return $this;
    }

    public function __toString()
    {
        return json_encode(get_object_vars($this));
    }
}
