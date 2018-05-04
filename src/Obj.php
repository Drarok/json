<?php

namespace Zerifas\JSON;

class Obj extends Value implements CollectionValue
{
    use CollectionTrait;

    public function __construct(array $schema = null)
    {
        parent::__construct();
        $this->setSchema($schema);
    }
}
