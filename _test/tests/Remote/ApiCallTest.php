<?php

namespace dokuwiki\test\Remote;

use dokuwiki\Remote\ApiCall;
use dokuwiki\Remote\OpenApiDoc\DocBlockMethod;

class ApiCallTest extends \DokuWikiTest
{
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
    public function dummyMethod1($foo, $bar, $baz, $boink = 'boink')
    {
        return $foo . $bar . implode('', $baz) . $boink;
    }

    public function testMethodDocBlock()
    {
        $call = new ApiCall([$this, 'dummyMethod1'], 'cat1');

        // basic doc block tests. More tests are done in the docblock parser class tests
        $this->assertEquals('This is a test', $call->getSummary());
        $this->assertEquals("With more information\nin several lines", $call->getDescription());
        $args = $call->getArgs();
        $this->assertIsArray($args);
        $this->assertArrayHasKey('foo', $args);
        $docs = $call->getDocs();
        $this->assertInstanceOf(DocBlockMethod::class, $docs);

        // test public access
        $this->assertFalse($call->isPublic());
        $call->setPublic();
        $this->assertTrue($call->isPublic());

        // check category
        $this->assertEquals('cat1', $call->getCategory());
    }

    public function testFunctionDocBlock()
    {
        $call = new ApiCall('inlineSVG');

        // basic doc block tests. More tests are done in the docblock parser class tests
        $args = $call->getArgs();
        $this->assertIsArray($args);
        $this->assertArrayHasKey('file', $args);
        $docs = $call->getDocs();
        $this->assertInstanceOf(DocBlockMethod::class, $docs);

        // check category (not set)
        $this->assertEquals('', $call->getCategory());
    }

    public function testExecution()
    {
        $call = new ApiCall([$this, 'dummyMethod1']);
        $this->assertEquals(
            'bar1molfhaha',
            $call(['bar', 1, ['molf'], 'haha']),
            'positional parameters'
        );
        $this->assertEquals(
            'bar1molfhaha',
            $call(['foo' => 'bar', 'bar' => 1, 'baz' => ['molf'], 'boink' => 'haha']),
            'named parameters'
        );

        $this->assertEquals(
            'bar1molfboink',
            $call(['bar', 1, ['molf']]),
            'positional parameters, missing optional'
        );
        $this->assertEquals(
            'bar1molfboink',
            $call(['foo' => 'bar', 'bar' => 1, 'baz' => ['molf']]),
            'named parameters, missing optional'
        );
        $this->assertEquals(
            'bar1molfboink',
            $call(['foo' => 'bar', 'bar' => 1, 'baz' => ['molf'],'nope' => 'egal']),
            'named parameters, missing optional, additional unknown'
        );

        $call = new ApiCall('date');
        $this->assertEquals('2023-11-30', $call(['Y-m-d', 1701356591]), 'positional parameters');
        $this->assertEquals('2023-11-30', $call(['format' => 'Y-m-d', 'timestamp' => 1701356591]), 'named parameters');
    }

    public function testCallMissingPositionalParameter()
    {
        $call = new ApiCall([$this, 'dummyMethod1']);
        $this->expectException(\ArgumentCountError::class);
        $call(['bar']);
    }

    public function testCallMissingNamedParameter()
    {
        $call = new ApiCall([$this, 'dummyMethod1']);
        $this->expectException(\ArgumentCountError::class);
        $call(['foo' => 'bar', 'baz'=> ['molf']]); // missing bar
    }
}
