<?php

namespace Zerifas\JSON;

class Arr extends Value implements CollectionValue
{
    use CollectionTrait;

    public function __construct($schema = null)
    {
        if ($schema !== null) {
            $schema = [$schema];
        }
        parent::__construct();
        $this->setSchema($schema);
    }
}
