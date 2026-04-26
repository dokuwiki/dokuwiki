<?php

namespace dokuwiki\test\Parsing\ParserMode;

use dokuwiki\Parsing\ParserMode\Eol;
use dokuwiki\Parsing\ParserMode\Linebreak;

class EolTest extends ParserTestBase
{

    function testEol() {
        $this->P->addMode('eol',new Eol());
        $this->P->parse("Foo\nBar");
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["Foo"."\n"."Bar"]],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testEolMultiple() {
        $this->P->addMode('eol',new Eol());
        $this->P->parse("Foo\n\nbar\nFoo");
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["Foo"]],
            ['p_close',[]],
            ['p_open',[]],
            ['cdata',["bar"."\n"."Foo"]],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testWinEol() {
        $this->P->addMode('eol',new Eol());
        $this->P->parse("Foo\r\nBar");
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["Foo"."\n"."Bar"]],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testLinebreak() {
        $this->P->addMode('linebreak',new Linebreak());
        $this->P->parse('Foo\\\\ Bar');
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\nFoo"]],
            ['linebreak',[]],
            ['cdata',["Bar"]],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testLinebreakPlusEol() {
        $this->P->addMode('linebreak',new Linebreak());
        $this->P->addMode('eol',new Eol());
        $this->P->parse('Foo\\\\'."\n\n".'Bar');

        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["Foo"]],
            ['linebreak',[]],
            ['p_close',[]],
            ['p_open',[]],
            ['cdata',["Bar"]],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testLinebreakInvalid() {
        $this->P->addMode('linebreak',new Linebreak());
        $this->P->parse('Foo\\\\Bar');
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo\\\\Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

}
