<?php

namespace dokuwiki\test\Remote;

use dokuwiki\Remote\ApiCall;

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
        $call = new ApiCall([$this, 'dummyMethod1']);

        $this->assertEquals('This is a test', $call->getSummary());
        $this->assertEquals("With more information\nin several lines", $call->getDescription());

        $this->assertEquals(
            [
                'foo' => [
                    'type' => 'string',
                    'description' => 'First variable',
                ],
                'bar' => [
                    'type' => 'int',
                    'description' => '',
                ],
                'baz' => [
                    'type' => 'array',
                    'description' => '',
                ],
            ],
            $call->getArgs()
        );

        $this->assertEquals(
            [
                'type' => 'string',
                'description' => 'The return'
            ],
            $call->getReturn()
        );

        // remove one parameter
        $call->limitArgs(['foo']);
        $this->assertEquals(
            [
                'foo' => [
                    'type' => 'string',
                    'description' => 'First variable',
                ],
            ],
            $call->getArgs()
        );
    }

    public function testFunctionDocBlock()
    {
        $call = new ApiCall('inlineSVG');
        $call->setArgDescription('file', 'overwritten description');

        $this->assertEquals(
            [
                'file' => [
                    'type' => 'string',
                    'description' => 'overwritten description',
                ],
                'maxsize' => [
                    'type' => 'int',
                    'description' => 'maximum allowed size for the SVG to be embedded',
                ]
            ],
            $call->getArgs()
        );
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
