<?php

namespace dokuwiki\test\Parsing\ParserMode;

use dokuwiki\Parsing\ParserMode\Notoc;

class NotocTest extends ParserTestBase
{
    function testNotoc()
    {
        $this->P->addMode('notoc', new Notoc());
        $this->P->parse('Foo ~~NOTOC~~ Bar');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nFoo "]],
            ['notoc', []],
            ['cdata', [' Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testNotocNotInline()
    {
        $this->P->addMode('notoc', new Notoc());
        $this->P->parse('Foo ~~notoc~~ Bar');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nFoo ~~notoc~~ Bar"]],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }
}
