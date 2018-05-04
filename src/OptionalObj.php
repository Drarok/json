<?php

namespace Zerifas\JSON;

class OptionalObj extends OptionalValue implements CollectionValue
{
    use CollectionTrait;

    public function __construct(array $schema = null, array $default = null)
    {
        if ($default !== null) {
            $default = (object) $default;
        }
        parent::__construct($default);

        $this->setSchema($schema);
    }
}
