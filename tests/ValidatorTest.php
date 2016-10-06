<?php

namespace Zerifas\JSON\Test;

use Zerifas\JSON;

class ValidatorTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function testSimpleValidation()
    {
        $schema = new JSON\Object([
            'requiredString'  => new JSON\String(),
            'requiredNumber'  => new JSON\Number(),
            'requiredObject'  => new JSON\Object([]),
            'requiredArray'   => new JSON\Arr(),
            'requiredBoolean' => new JSON\Boolean(),
        ]);

        $json = json_encode([
            'requiredString'  => 'Hello',
            'requiredNumber'  => 15,
            'requiredObject'  => (object) [],
            'requiredArray'   => [15],
            'requiredBoolean' => true,
        ]);

        $validator = new JSON\Validator($schema);
        $valid = $validator->isValid($json);
        if (! $valid) {
            throw new \Exception(implode(', ', $validator->getErrors()));
        }
        $this->assertTrue($valid);
    }

    public function testMissingRequiredValue()
    {
        $schema = new JSON\Object([
            'required' => new JSON\String(),
        ]);

        $json = json_encode([
            'other' => 'value',
        ]);

        $errors = [
            'Key path \'required\' is required, but missing.',
        ];

        $validator = new JSON\Validator($schema);
        $this->assertFalse($validator->isValid($json));
        $this->assertEquals($errors, $validator->getErrors());
    }

    public function testIncorrectRequiredValue()
    {
        $schema = new JSON\Object([
            'required' => new JSON\String(),
        ]);

        $json = json_encode([
            'required' => 15,
        ]);

        $errors = [
            'Key path \'required\' should be string, but is number.',
        ];

        $validator = new JSON\Validator($schema);
        $this->assertFalse($validator->isValid($json));
        $this->assertEquals($errors, $validator->getErrors());
    }

    public function testOptionalValues()
    {
        $schema = new JSON\Object([
            'optionalNullString'     => new JSON\OptionalString(),
            'optionalDefaultString'  => new JSON\OptionalString('Hello, World!'),
            'optionalNullNumber'     => new JSON\OptionalNumber(),
            'optionalDefaultNumber'  => new JSON\OptionalNumber(15),
            'optionalNullObject'     => new JSON\OptionalObject([]),
            'optionalDefaultObject'  => new JSON\OptionalObject([], ['key' => 'value']),
            'optionalNullArray'      => new JSON\OptionalArr(),
            'optionalDefaultArray'   => new JSON\OptionalArr(null, [1, 2, 3]),
            'optionalNullBoolean'    => new JSON\OptionalBoolean(),
            'optionalDefaultBoolean' => new JSON\OptionalBoolean(true),
        ]);

        $json = json_encode([
        ]);

        $expected = (object) [
            'optionalNullString'     => null,
            'optionalDefaultString'  => 'Hello, World!',
            'optionalNullNumber'     => null,
            'optionalDefaultNumber'  => 15,
            'optionalNullObject'     => null,
            'optionalDefaultObject'  => (object) ['key' => 'value'],
            'optionalNullArray'      => null,
            'optionalDefaultArray'   => [1, 2, 3],
            'optionalNullBoolean'    => null,
            'optionalDefaultBoolean' => true,
        ];

        $validator = new JSON\Validator($schema);
        $this->assertTrue($validator->isValid($json));
        $this->assertEquals($expected, $validator->getDocument());
    }

    public function testRecursion()
    {
        $schema = new JSON\Object([
            'result' => new JSON\Object([
                'values' => new JSON\Arr(),
            ]),
        ]);

        $json = json_encode([
            'result' => [
                'values' => [1, 2, 3],
            ],
        ]);

        $validator = new JSON\Validator($schema);
        $this->assertTrue($validator->isValid($json));
        $this->assertEquals([1, 2, 3], $validator->getDocument()->result->values);
    }

    public function testValidUniformArrays()
    {
        $schema = new JSON\Object([
            'keys'  => new JSON\Arr(new JSON\Number()),
            'posts' => new JSON\Arr(
                new JSON\Object([
                    'id' => new JSON\String(),
                    'extra' => new JSON\OptionalBoolean(),
                ])
            ),
            'users' => new JSON\OptionalArr(null, [1, 2, 3]),
        ]);

        $keys = [
            1, 2, 3, 4,
            15, 42,
        ];

        $posts = [
            ['id' => '23423-23423423-2344322', 'extra' => true],
            ['id' => '23423-23423423-2344324'],
            ['id' => '23423-23423423-2344364'],
            ['id' => '23423-23423423-2348364'],
        ];
        $jsonPosts = [
            (object) ['id' => '23423-23423423-2344322', 'extra' => true],
            (object) ['id' => '23423-23423423-2344324', 'extra' => null],
            (object) ['id' => '23423-23423423-2344364', 'extra' => null],
            (object) ['id' => '23423-23423423-2348364', 'extra' => null],
        ];

        $json = json_encode([
            'keys'  => $keys,
            'posts' => $posts,
        ]);

        $validator = new JSON\Validator($schema);
        $valid = $validator->isValid($json);
        if (! $valid) {
            var_dump($validator->getErrors());
        }
        $this->assertTrue($valid);

        $doc = $validator->getDocument();
        $this->assertEquals(15, $doc->keys[4]);
        $this->assertEquals($jsonPosts, $doc->posts);
        $this->assertEquals([1, 2, 3], $doc->users);
    }

    public function testInvalidUniformArrays()
    {
        $schema = new JSON\Object([
            'keys'  => new JSON\Arr(new JSON\Number()),
        ]);

        $keys = [
            1, 2, 3, 4, 'test',
            15, 42,
        ];

        $json = json_encode([
            'keys'  => $keys,
        ]);

        $errors = [
            'Key path \'keys[4]\' should be number, but is string.',
        ];

        $validator = new JSON\Validator($schema);
        $this->assertFalse($validator->isValid($json));
        $this->assertEquals($errors, $validator->getErrors());
    }

    public function testNonUniformArray()
    {
        $schema = new JSON\Object([
            'keys'  => new JSON\Arr(),
        ]);

        $keys = [
            1, 2, 3, 4, 'test',
            15, 42,
        ];

        $json = json_encode([
            'keys'  => $keys,
        ]);

        $validator = new JSON\Validator($schema);
        $this->assertTrue($validator->isValid($json));
        $this->assertEquals($keys, $validator->getDocument()->keys);
    }

    public function testValidUniformRoot()
    {
        $schema = new JSON\Arr(new JSON\Number());

        $keys = [
            1, 2, 3, 4,
            15, 42,
        ];

        $json = json_encode($keys);

        $validator = new JSON\Validator($schema);
        $this->assertTrue($validator->isValid($json));
        $this->assertEquals($keys, $validator->getDocument());
    }

    public function testInvalidUniformRoot()
    {
        $schema = new JSON\Arr(new JSON\Number());

        $keys = [
            1, 2, 3, 4, 'test',
            15, 42,
        ];

        $json = json_encode($keys);

        $errors = [
            'Key path \'[4]\' should be number, but is string.',
        ];

        $validator = new JSON\Validator($schema);
        $this->assertFalse($validator->isValid($json));
        $this->assertEquals($errors, $validator->getErrors());
    }

    public function testNonUniformRoot()
    {
        $schema = new JSON\Arr();

        $keys = [
            1, 2, 3, 4, 'test',
            15, 42,
        ];

        $json = json_encode($keys);

        $validator = new JSON\Validator($schema);
        $this->assertTrue($validator->isValid($json));
        $this->assertEquals($keys, $validator->getDocument());
    }

    public function testEmptyArray()
    {
        $schema = new JSON\Arr();
        $keys = [];
        $json = json_encode($keys);

        $validator = new JSON\Validator($schema);
        $this->assertTrue($validator->isValid($json));
        $this->assertEquals($keys, $validator->getDocument());
    }

    public function testEmptyUniformArray()
    {
        $schema = new JSON\Arr(new JSON\Number());
        $keys = [];
        $json = json_encode($keys);

        $validator = new JSON\Validator($schema);
        $this->assertTrue($validator->isValid($json));
        $this->assertEquals($keys, $validator->getDocument());
    }
}
