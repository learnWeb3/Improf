<?php

namespace Application\Core;

use Application\Helper\ObjectUtils;

class CollectionRecord
{
    public array $values;
    public function __construct(array $values)
    {
        $this->values = $values;
    }

    public function __toString()
    {
        return json_encode(
            array_map(function ($collection_element) {
                if (gettype($collection_element) === "object") {
                    return ObjectUtils::getPublicVars($collection_element);
                } else {
                    return $collection_element;
                }
            }, $this->values)
        );
    }
}
