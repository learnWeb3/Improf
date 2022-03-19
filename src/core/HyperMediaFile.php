<?php

namespace Application\Core;

class HyperMediaFile
{
    private $name;
    private $type;
    private $path;
    private $errors;

    public function __construct(string|null $name, string $type, string $path, array $error)
    {
        $this->name = $name;
        $this->type = $type;
        $this->path = $path;
        $this->error = $error;
    }

    /**
     * Get the value of name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set the value of name
     *
     * @return  self
     */
    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the value of type
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Set the value of type
     *
     * @return  self
     */
    public function setType(string $type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get the value of path
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Set the value of path
     *
     * @return  self
     */
    public function setPath(string $path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get the value of errors
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Set the value of errors
     *
     * @return  self
     */
    public function setErrors(array $errors)
    {
        $this->errors = $errors;

        return $this;
    }
}
