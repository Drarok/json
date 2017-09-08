# JSON [![Build Status](https://travis-ci.org/Drarok/json.svg?branch=develop)](https://travis-ci.org/Drarok/json) [![Coverage Status](https://coveralls.io/repos/github/Drarok/json/badge.svg)](https://coveralls.io/github/Drarok/json)

This is a small library allowing you to define a schema for your JSON documents, validate them, and get a "safe" version of the document, with all optional values set to their defaults.

## Installation

Use [composer](https://getcomposer.org):

```bash
$ composer required zerifas/json
```

## Usage

```php
<?php

require 'vendor/autoload.php';

use Zerifas\JSON;

$schema = new JSON\Object([
    'id' => new JSON\Number(),
    'enabled' => new JSON\OptionalBoolean(false),
    'array' => new JSON\Arr(),
    'stringArray' => new JSON\Arr(new JSON\Str()),
    'optionalArray' => new JSON\OptionalArr(),
    'optionalStringArray' => new JSON\OptionalArr(new JSON\Str()),
    'optionalObject' => new JSON\OptionalObject(
        [
            'name' => new JSON\Str(),
        ],
        [
            'name' => 'Alice',
        ]
    ),
]);
$v = new JSON\Validator($schema);

$json = '{"id":1,"array":[],"stringArray":["Hello","World"]}';
if ($v->isValid($json)) {
    $doc = $v->getDocument();
    echo implode(', ', $doc->stringArray), PHP_EOL; // Hello, World
    echo $doc->optionalObject->name, PHP_EOL; // Alice
}

// This is not valid for 2 reasons: `id` is missing, and `array` is a number.
$json = '{"array":15,"stringArray":[]}';
if (!$v->isValid($json)) {
    // Errors will be an array:
    // [
    //     'Key path \'id\' is required, but missing.',
    //     'Key path \'array\' should be array, but is number.',
    // ]
    foreach ($v->getErrors() as $err) {
        echo $err, PHP_EOL;
    }
}
```
