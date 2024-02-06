<?php

namespace dokuwiki\test\Remote\OpenApiDoc;

use dokuwiki\Remote\OpenApiDoc\Type;

class TypeTest extends \DokuWikiTest
{

    public function provideBaseTypes()
    {
        return [
            // [hint, jsonrpc, xmlrpc, context]
            ['string', 'string', 'string'],
            ['string', 'string', 'string', self::class],
            ['int', 'int', 'int'],
            ['int', 'int', 'int', self::class],
            ['file', 'string', 'file'],
            ['file', 'string', 'file', self::class],
            ['date', 'int', 'date'],
            ['date', 'int', 'date', self::class],
            ['boolean', 'bool', 'bool'],
            ['boolean', 'bool', 'bool', self::class],
            ['false', 'bool', 'bool'],
            ['false', 'bool', 'bool', self::class],
            ['true', 'bool', 'bool'],
            ['true', 'bool', 'bool', self::class],
            ['integer', 'int', 'int'],
            ['integer', 'int', 'int', self::class],
            ['array', 'array', 'array'],
            ['array', 'array', 'array', self::class],
            ['array[]', 'array', 'array'],
            ['array[]', 'array', 'array', self::class],
            ['foo', 'foo', 'object'],
            ['foo', 'foo', 'object', self::class],
            ['foo[]', 'array', 'array'],
            ['foo[]', 'array', 'array', self::class],
            ['Foo', 'Foo', 'object'],
            ['Foo', 'dokuwiki\\test\\Remote\\OpenApiDoc\\Foo', 'object', self::class],
            ['\\Foo', 'Foo', 'object'],
            ['\\Foo', 'Foo', 'object', self::class],
        ];
    }

    /**
     * @dataProvider provideBaseTypes
     * @param $typehint
     * @param $expectedJSONRPCType
     * @param $expectedXMLRPCType
     * @param $context
     * @return void
     */
    public function testJSONBaseTypes($typehint, $expectedJSONRPCType, $expectedXMLRPCType, $context = '')
    {
        $type = new Type($typehint, $context);
        $this->assertEquals($expectedJSONRPCType, $type->getJSONRPCType());
    }

    /**
     * @dataProvider provideBaseTypes
     * @param $typehint
     * @param $expectedJSONRPCType
     * @param $expectedXMLRPCType
     * @param $context
     * @return void
     */
    public function testXMLBaseTypes($typehint, $expectedJSONRPCType, $expectedXMLRPCType, $context = '')
    {
        $type = new Type($typehint, $context);
        $this->assertEquals($expectedXMLRPCType, $type->getXMLRPCType());
    }

    public function provideSubTypes()
    {
        return [
            ['string', ['string']],
            ['string[]', ['array', 'string']],
            ['string[][]', ['array', 'array', 'string']],
            ['array[][]', ['array', 'array', 'array']],
            ['Foo[][]', ['array', 'array', 'Foo']],
            ['Foo[][]', ['array', 'array', 'dokuwiki\\test\\Remote\\OpenApiDoc\\Foo'], self::class],
        ];
    }

    /**
     * @dataProvider provideSubTypes
     * @param $typehint
     * @param $expected
     * @param $context
     * @return void
     */
    public function testSubType($typehint, $expected, $context = '')
    {
        $type = new Type($typehint, $context);

        $result = [$type->getJSONRPCType()];
        while ($type = $type->getSubType()) {
            $result[] = $type->getJSONRPCType();
        }

        $this->assertEquals($expected, $result);
    }

}
