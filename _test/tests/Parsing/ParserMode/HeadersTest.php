<?php

namespace dokuwiki\test\Parsing\ParserMode;

use dokuwiki\Parsing\ParserMode\Eol;
use dokuwiki\Parsing\ParserMode\Header;

class HeadersTest extends ParserTestBase
{

    function testHeader1() {
        $this->P->addMode('header',new Header());
        $this->P->parse("abc \n ====== Header ====== \n def");
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\nabc "]],
            ['p_close',[]],
            ['header',['Header',1,6]],
            ['section_open',[1]],
            ['p_open',[]],
            ['cdata',["\n def"]],
            ['p_close',[]],
            ['section_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testHeader2() {
        $this->P->addMode('header',new Header());
        $this->P->parse("abc \n  ===== Header ===== \n def");
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\nabc "]],
            ['p_close',[]],
            ['header',['Header',2,6]],
            ['section_open',[2]],
            ['p_open',[]],
            ['cdata',["\n def"]],
            ['p_close',[]],
            ['section_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testHeader3() {
        $this->P->addMode('header',new Header());
        $this->P->parse("abc \n ==== Header ==== \n def");
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\nabc "]],
            ['p_close',[]],
            ['header',['Header',3,6]],
            ['section_open',[3]],
            ['p_open',[]],
            ['cdata',["\n def"]],
            ['p_close',[]],
            ['section_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testHeader4() {
        $this->P->addMode('header',new Header());
        $this->P->parse("abc \n === Header === \n def");
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\nabc "]],
            ['p_close',[]],
            ['header',['Header',4,6]],
            ['section_open',[4]],
            ['p_open',[]],
            ['cdata',["\n def"]],
            ['p_close',[]],
            ['section_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testHeader5() {
        $this->P->addMode('header',new Header());
        $this->P->parse("abc \n  == Header ==  \n def");
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\nabc "]],
            ['p_close',[]],
            ['header',['Header',5,6]],
            ['section_open',[5]],
            ['p_open',[]],
            ['cdata',["\n def"]],
            ['p_close',[]],
            ['section_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testHeader2UnevenSmaller() {
        $this->P->addMode('header',new Header());
        $this->P->parse("abc \n  ===== Header ==  \n def");
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\nabc "]],
            ['p_close',[]],
            ['header',['Header',2,6]],
            ['section_open',[2]],
            ['p_open',[]],
            ['cdata',["\n def"]],
            ['p_close',[]],
            ['section_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testHeader2UnevenBigger() {
        $this->P->addMode('header',new Header());
        $this->P->parse("abc \n  ===== Header ===========  \n def");
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\nabc "]],
            ['p_close',[]],
            ['header',['Header',2,6]],
            ['section_open',[2]],
            ['p_open',[]],
            ['cdata',["\n def"]],
            ['p_close',[]],
            ['section_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testHeaderLarge() {
        $this->P->addMode('header',new Header());
        $this->P->parse("abc \n ======= Header ======= \n def");
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\nabc "]],
            ['p_close',[]],
            ['header',['Header',1,6]],
            ['section_open',[1]],
            ['p_open',[]],
            ['cdata',["\n def"]],
            ['p_close',[]],
            ['section_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testHeaderSmall() {
        $this->P->addMode('header',new Header());
        $this->P->parse("abc \n= Header =\n def");
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\nabc \n= Header =\n def"]],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }


    function testHeader1Mixed() {
        $this->P->addMode('header',new Header());
        $this->P->parse("abc \n====== == Header == ======\n def");
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\nabc "]],
            ['p_close',[]],
            ['header',['== Header ==',1,6]],
            ['section_open',[1]],
            ['p_open',[]],
            ['cdata',["\n def"]],
            ['p_close',[]],
            ['section_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testHeader5Mixed() {
        $this->P->addMode('header',new Header());
        $this->P->parse("abc \n== ====== Header ====== ==\n def");
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\nabc "]],
            ['p_close',[]],
            ['header',['====== Header ======',5,6]],
            ['section_open',[5]],
            ['p_open',[]],
            ['cdata',["\n def"]],
            ['p_close',[]],
            ['section_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testHeaderMultiline() {
        $this->P->addMode('header',new Header());
        $this->P->parse("abc \n== ====== Header\n ====== ==\n def");
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\nabc \n== ====== Header"]],
            ['p_close',[]],
            ['header',['',1,23]],
            ['section_open',[1]],
            ['p_open',[]],
            ['cdata',["\n def"]],
            ['p_close',[]],
            ['section_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testHeader1Eol() {
        $this->P->addMode('header',new Header());
        $this->P->addMode('eol',new Eol());
        $this->P->parse("abc \n ====== Header ====== \n def");
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',['abc']],
            ['p_close',[]],
            ['header',['Header',1, 6]],
            ['section_open',[1]],
            ['p_open',[]],
            ['cdata',['def']],
            ['p_close',[]],
            ['section_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);

    }

    function testNoMidLineHeader() {
        // a `== .. ==` run that follows other text on the line is plain text;
        // a heading must start the line (only whitespace may precede it)
        $this->P->addMode('header',new Header());
        $this->P->parse("a foo ====== bar ======\n");
        $modes = array_column($this->H->calls, 0);
        $this->assertNotContains('header', $modes,
            'a heading must occupy its own line, not follow text mid-line');
    }

    function testSurroundingWhitespaceStillMatches() {
        // leading/trailing whitespace around the `=` run is allowed: the
        // heading still occupies its own line
        $this->P->addMode('header',new Header());
        $this->P->parse("abc\n \t ====== Header ======  \ndef");
        $headers = array_values(array_filter(
            $this->H->calls,
            static fn($c) => $c[0] === 'header'
        ));
        $this->assertCount(1, $headers, 'whitespace around the heading is allowed');
        $this->assertSame('Header', $headers[0][1][0]);
        $this->assertSame(1, $headers[0][1][1]);
    }

    function testHeaderMulti2() {
        $this->P->addMode('header',new Header());
        $this->P->parse("abc \n ====== Header ====== \n def abc \n ===== Header2 ===== \n def");
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\nabc "]],
            ['p_close',[]],
            ['header',['Header',1,6]],
            ['section_open',[1]],
            ['p_open',[]],
            ['cdata',["\n def abc "]],
            ['p_close',[]],
            ['section_close',[]],
            ['header',['Header2',2,39]],
            ['section_open',[2]],
            ['p_open',[]],
            ['cdata',["\n def"]],
            ['p_close',[]],
            ['section_close',[]],
            ['document_end',[]]
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

}
