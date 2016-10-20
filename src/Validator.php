<?php

namespace Zerifas\JSON;

class Validator
{
    protected $collection;
    protected $strict;
    protected $errors;
    protected $document;

    public function __construct(CollectionValue $collection, $strict = false)
    {
        $this->collection = $collection;
        $this->strict = $strict;
    }

    public function isValid($json)
    {
        $this->errors = [];
        $this->document = $this->validate(json_decode($json));
        return count($this->errors) === 0;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getDocument()
    {
        return $this->document;
    }

    protected function validate($obj, CollectionValue $collection = null, $keyPath = '')
    {
        if ($collection === null) {
            $collection = $this->collection;
        }

        $schema = $collection->getSchema();

        $isArray = (
            ($collection instanceof Arr) ||
            ($collection instanceof OptionalArr)
        );

        if ($isArray) {
            if (! $obj) {
                return [];
            }

            if (! $schema) {
                $rule = null;
            } else {
                $rule = $schema[0];
            }

            $schema = array_fill(0, count($obj), $rule);
        }

        $document = [];
        $recursionQueue = [];

        foreach ($schema as $key => $value) {
            if ($isArray) {
                $currentKeyPath = sprintf('%s[%d]', trim($keyPath, '.'), $key);
                $jsonValue = array_key_exists($key, $obj) ? $obj[$key] : null;
            } else {
                $currentKeyPath = trim($keyPath . '.' . $key, '.');
                $jsonValue = isset($obj->$key) ? $obj->$key : null;
            }

            if ($jsonValue === null) {
                if ($value->isRequired()) {
                    $this->addError('Key path \'%s\' is required, but missing.', $currentKeyPath);
                } else {
                    $document[$key] = $value->getDefault();
                }

                continue;
            }

            if ($value !== null) {
                // There is a value, so check it.
                $expectedType = Value::getType($value);
                $actualType = Value::getType($jsonValue);

                if ($expectedType !== $actualType) {
                    $this->addError(
                        'Key path \'%s\' should be %s, but is %s.',
                        $currentKeyPath,
                        $expectedType,
                        $actualType
                    );
                    continue;
                }
            }

            if ($value instanceof CollectionValue && $value->getSchema()) {
                // We want to check all of the keys at the current level before recursing,
                // so we add them into a queue which is executed after strict mode checks.
                $recursionQueue[] = function (&$document) use ($key, $jsonValue, $value, $currentKeyPath) {
                    $document[$key] = $this->validate($jsonValue, $value, $currentKeyPath);
                };
            } else {
                $document[$key] = $jsonValue;
            }
        }

        $isObject = (
            $collection instanceof Object ||
            $collection instanceof OptionalObject
        );

        if ($this->strict && $isObject) {
            $extraKeys = array_diff(array_keys((array) $obj), array_keys($schema));
            foreach ($extraKeys as $k) {
                $this->addError('Key path \'%s\' is unexpected in strict mode.', trim($keyPath . '.' . $k, '.'));
            }
        }

        foreach ($recursionQueue as $fn) {
            $fn($document);
        }

        return $isArray ? $document : (object) $document;
    }

    protected function addError()
    {
        $args = func_get_args();
        $fmt = array_shift($args);
        $this->errors[] = vsprintf($fmt, $args);
    }
}
