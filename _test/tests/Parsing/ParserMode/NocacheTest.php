<?php

namespace dokuwiki\test\Parsing\ParserMode;

use dokuwiki\Parsing\ParserMode\Nocache;

class NocacheTest extends ParserTestBase
{
    function testNocache()
    {
        $this->P->addMode('nocache', new Nocache());
        $this->P->parse('Foo ~~NOCACHE~~ Bar');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nFoo "]],
            ['nocache', []],
            ['cdata', [' Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testNocacheNotInline()
    {
        $this->P->addMode('nocache', new Nocache());
        $this->P->parse('Foo ~~nocache~~ Bar');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nFoo ~~nocache~~ Bar"]],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }
}
