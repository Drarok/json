<?php

namespace Zerifas\JSON;

trait CollectionTrait
{
    protected $schema;

    protected function setSchema(array $schema = null)
    {
        if ($schema) {
            foreach ($schema as $key => $value) {
                if (! ($value instanceof Value)) {
                    throw new \InvalidArgumentException(sprintf(
                        '%s schema values must be instances of %s, not %s',
                        static::class,
                        Value::class,
                        is_object($value) ? get_class($value) : gettype($value)
                    ));
                }
            }
        }
        $this->schema = $schema;
    }

    public function getSchema()
    {
        return $this->schema;
    }
}
