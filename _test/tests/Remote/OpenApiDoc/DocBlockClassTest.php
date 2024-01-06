<?php

namespace dokuwiki\test\Remote\OpenApiDoc;

use dokuwiki\Remote\OpenApiDoc\DocBlockClass;
use dokuwiki\Remote\OpenApiDoc\DocBlockMethod;
use dokuwiki\Remote\OpenApiDoc\DocBlockProperty;
use dokuwiki\Remote\OpenApiDoc\Type;

/**
 * Test cases for DocBlockClass
 *
 * This test class is also used in the tests itself
 */
class DocBlockClassTest extends \DokuWikiTest
{
    /** @var string This is a dummy */
    public $dummyProperty1 = 'dummy';

    /**
     * Parse this test class with the DocBlockClass
     *
     * Also tests property and method access
     *
     * @return void Tests don't return anything
     */
    public function testClass()
    {
        $reflect = new \ReflectionClass($this);
        $doc = new DocBlockClass($reflect);

        $this->assertStringContainsString('Test cases for DocBlockClass', $doc->getSummary());
        $this->assertStringContainsString('used in the tests itself', $doc->getDescription());

        $property = $doc->getPropertyDocs()['dummyProperty1'];
        $this->assertInstanceOf(DocBlockProperty::class, $property);
        $this->assertEquals('This is a dummy', $property->getSummary());

        $propertyType = $property->getType();
        $this->assertInstanceOf(Type::class, $propertyType);
        $this->assertEquals('string', $propertyType->getBaseType());

        $method = $doc->getMethodDocs()['testClass'];
        $this->assertInstanceOf(DocBlockMethod::class, $method);

        $methodReturn = $method->getReturn();
        $this->assertInstanceOf(Type::class, $methodReturn['type']);
        $this->assertEquals('Tests don\'t return anything', $methodReturn['description']);
    }

}
