<?php

namespace dokuwiki\test\Parsing\ParserMode;

use dokuwiki\Parsing\ParserMode\Eol;
use dokuwiki\Parsing\ParserMode\Quote;

class QuoteTest extends ParserTestBase
{

    function testQuote() {
        $this->P->addMode('quote',new Quote());
        $this->P->parse("abc\n> def\n>>ghi\nklm");
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\nabc"]],
            ['p_close',[]],
            ['quote_open',[]],
            ['cdata',[" def"]],
            ['quote_open',[]],
            ['cdata',["ghi"]],
            ['quote_close',[]],
            ['quote_close',[]],
            ['p_open',[]],
            ['cdata',["klm"]],
            ['p_close',[]],
            ['document_end',[]],

        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testQuoteWinCr() {
        $this->P->addMode('quote',new Quote());
        $this->P->parse("abc\r\n> def\r\n>>ghi\r\nklm");
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\nabc"]],
            ['p_close',[]],
            ['quote_open',[]],
            ['cdata',[" def"]],
            ['quote_open',[]],
            ['cdata',["ghi"]],
            ['quote_close',[]],
            ['quote_close',[]],
            ['p_open',[]],
            ['cdata',["klm"]],
            ['p_close',[]],
            ['document_end',[]],

        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testQuoteMinumumContext() {
        $this->P->addMode('quote',new Quote());
        $this->P->parse("\n> def\n>>ghi\n ");
        $calls = [
            ['document_start',[]],
            ['quote_open',[]],
            ['cdata',[" def"]],
            ['quote_open',[]],
            ['cdata',["ghi"]],
            ['quote_close',[]],
            ['quote_close',[]],
            ['document_end',[]],

        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testQuoteEol() {
        $this->P->addMode('quote',new Quote());
        $this->P->addMode('eol',new Eol());
        $this->P->parse("abc\n> def\n>>ghi\nklm");
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["abc"]],
            ['p_close',[]],
            ['quote_open',[]],
            ['cdata',[" def"]],
            ['quote_open',[]],
            ['cdata',["ghi"]],
            ['quote_close',[]],
            ['quote_close',[]],
            ['p_open',[]],
            ['cdata',["klm"]],
            ['p_close',[]],
            ['document_end',[]],

        ];
        $this->assertCalls($calls, $this->H->calls);
    }

}
