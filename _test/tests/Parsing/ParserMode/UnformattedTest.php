<?php

namespace dokuwiki\test\Parsing\ParserMode;

use dokuwiki\Parsing\ParserMode\Unformatted;

class UnformattedTest extends ParserTestBase
{

    function testNowiki() {
        $this->P->addMode('unformatted',new Unformatted());
        $this->P->parse("Foo <nowiki>testing</nowiki> Bar");
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo ']],
            ['unformatted',['testing']],
            ['cdata',[' Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];

        $this->assertCalls($calls, $this->H->calls);

    }

    function testDoublePercent() {
        $this->P->addMode('unformatted',new Unformatted());
        $this->P->parse("Foo %%testing%% Bar");
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo ']],
            ['unformatted',['testing']],
            ['cdata',[' Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }
}
