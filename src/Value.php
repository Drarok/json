<?php

namespace Zerifas\JSON;

abstract class Value
{
    const TYPE_STRING  = 'string';
    const TYPE_NUMBER  = 'number';
    const TYPE_OBJECT  = 'object';
    const TYPE_ARRAY   = 'array';
    const TYPE_BOOLEAN = 'boolean';
    const TYPE_NULL    = 'null';

    protected static $classMap = [
        Str::class             => self::TYPE_STRING,
        OptionalStr::class     => self::TYPE_STRING,
        Number::class          => self::TYPE_NUMBER,
        OptionalNumber::class  => self::TYPE_NUMBER,
        Object::class          => self::TYPE_OBJECT,
        OptionalObject::class  => self::TYPE_OBJECT,
        Arr::class             => self::TYPE_ARRAY,
        OptionalArr::class     => self::TYPE_ARRAY,
        Boolean::class         => self::TYPE_BOOLEAN,
        OptionalBoolean::class => self::TYPE_BOOLEAN,
    ];

    protected static $typeMap = [
        'string'  => self::TYPE_STRING,
        'integer' => self::TYPE_NUMBER,
        'double'  => self::TYPE_NUMBER,
        'object'  => self::TYPE_OBJECT,
        'array'   => self::TYPE_ARRAY,
        'boolean' => self::TYPE_BOOLEAN,
        'NULL'    => self::TYPE_NULL,
    ];

    public static function getType($v)
    {
        if (is_object($v) && $v instanceof Value) {
            $mapType = 'class';
            $key = get_class($v);
            $map = static::$classMap;
        } else {
            $mapType = 'type';
            $key = gettype($v);
            $map = static::$typeMap;
        }

        if (! array_key_exists($key, $map)) {
            throw new \InvalidArgumentException(sprintf(
                'Unknown %s: %s',
                $mapType,
                $key
            ));
        }

        return $map[$key];
    }

    public static function debug(Value $obj)
    {
        $class = explode('\\', get_class($obj));
        $class = end($class);

        return sprintf(
            '%s(%s)',
            $class,
            $obj->isRequired() ? '' : 'default = ' . json_encode($obj->getDefault())
        );
    }

    public function __construct()
    {
    }

    public function isRequired()
    {
        return true;
    }
}
