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
     * @something else
     * @something other
     * @another tag
     * @return string  The return
     */
    public function dummyMethod1($foo, $bar)
    {
        return 'dummy';
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
                ]
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
        $this->assertEquals('dummy', $call(['bar', 1]), 'positional parameters');
        $this->assertEquals('dummy', $call(['foo' => 'bar', 'bar' => 1]), 'named parameters');

        $call = new ApiCall('date');
        $this->assertEquals('2023-11-30', $call(['Y-m-d', 1701356591]), 'positional parameters');
        $this->assertEquals('2023-11-30', $call(['format' => 'Y-m-d', 'timestamp' => 1701356591]), 'named parameters');
    }
}
