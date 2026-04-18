<?php

namespace dokuwiki\test\Parsing\Lexer;

use dokuwiki\Parsing\Lexer\StateStack;

class StateStackTest extends \DokuWikiTest
{
    function testStartState()
    {
        $stack = new StateStack("one");
        $this->assertEquals("one", $stack->getCurrent());
    }

    function testExhaustion()
    {
        $stack = new StateStack("one");
        $this->assertFalse($stack->leave());
    }

    function testStateMoves()
    {
        $stack = new StateStack("one");
        $stack->enter("two");
        $this->assertEquals("two", $stack->getCurrent());
        $stack->enter("three");
        $this->assertEquals("three", $stack->getCurrent());
        $this->assertTrue($stack->leave());
        $this->assertEquals("two", $stack->getCurrent());
        $stack->enter("third");
        $this->assertEquals("third", $stack->getCurrent());
        $this->assertTrue($stack->leave());
        $this->assertTrue($stack->leave());
        $this->assertEquals("one", $stack->getCurrent());
    }
}
