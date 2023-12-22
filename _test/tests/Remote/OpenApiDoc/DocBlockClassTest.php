<?php

namespace dokuwiki\test\Remote\OpenApiDoc;

use dokuwiki\Remote\OpenApiDoc\DocBlockClass;
use dokuwiki\Remote\OpenApiDoc\DocBlockMethod;
use dokuwiki\Remote\OpenApiDoc\DocBlockProperty;

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
     * @return void
     */
    public function testClass()
    {
        $reflect = new \ReflectionClass($this);
        $doc = new DocBlockClass($reflect);

        $this->assertStringContainsString('Test cases for DocBlockClass', $doc->getSummary());
        $this->assertStringContainsString('used in the tests itself', $doc->getDescription());

        $this->assertInstanceOf(DocBlockProperty::class, $doc->getPropertyDocs()['dummyProperty1']);
        $this->assertEquals('This is a dummy', $doc->getPropertyDocs()['dummyProperty1']->getSummary());

        $this->assertInstanceOf(DocBlockMethod::class, $doc->getMethodDocs()['testClass']);
    }

}
