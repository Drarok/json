<?php

namespace Zerifas\JSON;

abstract class OptionalValue extends Value
{
    protected $default;

    public function __construct($default = null)
    {
        parent::__construct();
        $this->default = $default;
    }

    public function getDefault()
    {
        return $this->default;
    }

    public function isRequired()
    {
        return false;
    }
}
