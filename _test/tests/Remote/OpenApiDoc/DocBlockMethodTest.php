<?php

namespace dokuwiki\test\Remote\OpenApiDoc;

use dokuwiki\Remote\OpenApiDoc\DocBlockMethod;
use dokuwiki\Remote\OpenApiDoc\Type;

class DocBlockMethodTest extends \DokuWikiTest {


    /**
     * This is a test
     *
     * With more information
     * in several lines
     * @param string $foo First variable
     * @param int $bar
     * @param string[] $baz
     * @something else
     * @something other
     * @another tag
     * @return string  The return
     */
    public function dummyMethod1($foo, $bar, $baz=['a default'])
    {
        return 'dummy';
    }

    public function testMethod()
    {
        $reflect = new \ReflectionMethod($this, 'dummyMethod1');
        $doc = new DocBlockMethod($reflect);

        $this->assertEquals('This is a test', $doc->getSummary());
        $this->assertEquals("With more information\nin several lines", $doc->getDescription());

        $this->assertEquals(
            [
                'foo' => [
                    'type' => 'string',
                    'description' => 'First variable',
                    'optional' => false,
                ],
                'bar' => [
                    'type' => 'int',
                    'description' => '',
                    'optional' => false,
                ],
                'baz' => [
                    'type' => 'string[]',
                    'description' => '',
                    'optional' => true,
                    'default' => ['a default'],
                ],
            ],
            $doc->getTag('param')
        );

        $params = $doc->getParameters();
        $this->assertInstanceOf(Type::class, $params['foo']['type']);
        $this->assertInstanceOf(Type::class, $params['bar']['type']);
        $this->assertInstanceOf(Type::class, $params['baz']['type']);

        $this->assertEquals(
            [
                'type' => 'string',
                'description' => 'The return'
            ],
            $doc->getTag('return')
        );

        $return = $doc->getReturn();
        $this->assertInstanceOf(Type::class, $return['type']);

        $this->assertEquals(
            [
                'else',
                'other',
            ],
            $doc->getTag('something')
        );

        $this->assertEquals(
            [
                'tag',
            ],
            $doc->getTag('another')
        );


    }
}
