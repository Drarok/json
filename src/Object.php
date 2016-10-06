<?php

namespace Zerifas\JSON;

class Object extends Value implements CollectionValue
{
    use CollectionTrait;

    public function __construct(array $schema = null)
    {
        parent::__construct();
        $this->setSchema($schema);
    }
}
