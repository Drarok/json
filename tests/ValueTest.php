<?php

namespace Zerifas\JSON\Test;

use Zerifas\JSON;

class ValueTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider getAllJSONTypes
     */
    public function testAllJSONTypes($var, $expected)
    {
        $this->assertEquals($expected, JSON\Value::getType($var));
    }

    public function getAllJSONTypes()
    {
        return [
            ['x', 'string'],
            [1, 'number'],
            [1.23, 'number'],
            [new \stdClass(), 'object'],
            [[], 'array'],
            [true, 'boolean'],
            [false, 'boolean'],
            [null, 'null'],
        ];
    }

    /**
     * @dataProvider getAllValueTypes
     */
    public function testAllValueTypes($obj, $expected)
    {
        $this->assertEquals($expected, JSON\Value::getType($obj));

        if ($obj instanceof JSON\CollectionValue) {
            $this->assertEquals(null, $obj->getSchema());
        }
    }

    public function getAllValueTypes()
    {
        return [
            [new JSON\Str(), 'string'],
            [new JSON\OptionalStr(), 'string'],
            [new JSON\Number(), 'number'],
            [new JSON\OptionalNumber(), 'number'],
            [new JSON\Obj(), 'object'],
            [new JSON\OptionalObj(), 'object'],
            [new JSON\Arr(), 'array'],
            [new JSON\OptionalArr(), 'array'],
            [new JSON\Boolean(), 'boolean'],
            [new JSON\OptionalBoolean(), 'boolean'],
        ];
    }

    /**
     * @dataProvider getInvalidClasses
     */
    public function testInvalidClasses($class)
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown class: ');
        JSON\Value::getType(new $class());
    }

    public function getInvalidClasses()
    {
        return [
            [FakeValue::class],
            [OptionalFakeValue::class],
        ];
    }

    /**
     * @dataProvider getAllDebugValueTypes
     */
    public function testAllDebugValueTypes($obj, $expected)
    {
        $this->assertEquals($expected, JSON\Value::debug($obj));
    }

    public function getAllDebugValueTypes()
    {
        return [
            [new JSON\Str(), 'Str()'],
            [new JSON\OptionalStr(), 'OptionalStr(default = null)'],
            [new JSON\OptionalStr('def'), 'OptionalStr(default = "def")'],

            [new JSON\Number(), 'Number()'],
            [new JSON\OptionalNumber(), 'OptionalNumber(default = null)'],
            [new JSON\OptionalNumber(42), 'OptionalNumber(default = 42)'],

            [new JSON\Obj(), 'Obj()'],
            [new JSON\Obj([]), 'Obj()'],
            [new JSON\OptionalObj(), 'OptionalObj(default = null)'],
            [new JSON\OptionalObj([], null), 'OptionalObj(default = null)'],
            [new JSON\OptionalObj(null, []), 'OptionalObj(default = {})'],
            [new JSON\OptionalObj([], []), 'OptionalObj(default = {})'],
            [new JSON\OptionalObj([], ['key' => 'value']), 'OptionalObj(default = {"key":"value"})'],

            [new JSON\Arr(), 'Arr()'],
            [new JSON\OptionalArr(), 'OptionalArr(default = null)'],
            [new JSON\OptionalArr(null, []), 'OptionalArr(default = [])'],

            [new JSON\Boolean(), 'Boolean()'],
            [new JSON\OptionalBoolean(), 'OptionalBoolean(default = null)'],
            [new JSON\OptionalBoolean(false), 'OptionalBoolean(default = false)'],
        ];
    }

    /**
     *  @dataProvider getOptionalArrDefault
     */
    public function testOptionalArrDefault($default)
    {
        $obj = new JSON\OptionalArr(new JSON\Number(), $default);
        $this->assertEquals($default, $obj->getDefault());
    }

    public function getOptionalArrDefault()
    {
        return [
            [[]],
            [[1, 2, 3]],
            [['x', 'y', 'z']],
        ];
    }

    public function testInvalidArraySchema()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Zerifas\\JSON\\Arr schema values must be instances of Zerifas\\JSON\\Value'
        );
        $schema = new JSON\Arr(15);
    }
}
