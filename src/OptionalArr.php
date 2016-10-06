<?php

namespace Zerifas\JSON;

class OptionalArr extends OptionalValue implements CollectionValue
{
    use CollectionTrait;

    public function __construct($schema = null, array $default = null)
    {
        if ($schema !== null) {
            $schema = [$schema];
        }
        parent::__construct($default);
        $this->setSchema($schema);
    }
}
