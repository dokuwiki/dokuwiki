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

    public function provideUnionTypes()
    {
        return [
            // [hint, isUnion, isNullable, nonNullMembersAsStrings]
            ['int|bool', true, false, ['int', 'bool']],
            ['string|null', true, true, ['string']],
            ['null|string', true, true, ['string']],
            ['Page|null', true, true, ['Page']],
            ['int|bool|string', true, false, ['int', 'bool', 'string']],
            ['int|bool|null', true, true, ['int', 'bool']],
            // Malformed inputs degrade to non-union without throwing
            ['int|', false, false, ['int|']],
            ['|int', false, false, ['|int']],
            // Non-union types remain non-union
            ['int', false, false, ['int']],
            ['Foo[]', false, false, ['Foo[]']],
        ];
    }

    /**
     * @dataProvider provideUnionTypes
     */
    public function testUnionTypes($typehint, $isUnion, $isNullable, $expectedNonNull)
    {
        $type = new Type($typehint);
        $this->assertSame($isUnion, $type->isUnion(), 'isUnion');
        $this->assertSame($isNullable, $type->isNullable(), 'isNullable');
        $nonNull = array_map(fn(Type $t) => (string) $t, $type->getNonNullMembers());
        $this->assertEquals($expectedNonNull, $nonNull, 'non-null members');
    }

    public function provideMapTypes()
    {
        return [
            // [hint, isMap, expectedKey, expectedValue]
            ['array<string, Page>', true, 'string', 'Page'],
            ['array<int, string>', true, 'int', 'string'],
            ['array<string, int[]>', true, 'string', 'int[]'],
            ['array<string, array<int, Page>>', true, 'string', 'array<int, Page>'],
            // Non-maps
            ['array', false, null, null],
            ['array<>', false, null, null],
            ['array<int>', false, null, null],
            ['int[]', false, null, null],
            ['Page', false, null, null],
        ];
    }

    /**
     * @dataProvider provideMapTypes
     */
    public function testMapTypes($typehint, $isMap, $expectedKey, $expectedValue)
    {
        $type = new Type($typehint);
        $this->assertSame($isMap, $type->isMap(), 'isMap');
        if ($isMap) {
            $this->assertEquals($expectedKey, (string) $type->getMapKeyType());
            $this->assertEquals($expectedValue, (string) $type->getMapValueType());
        } else {
            $this->assertNull($type->getMapKeyType());
            $this->assertNull($type->getMapValueType());
        }
    }

}
