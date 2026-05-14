<?php

namespace dokuwiki\test\Parsing\ParserMode;

use dokuwiki\Parsing\ParserMode\Code;
use dokuwiki\Parsing\ParserMode\Eol;
use dokuwiki\Parsing\ParserMode\File;
use dokuwiki\Parsing\ParserMode\Header;
use dokuwiki\Parsing\ParserMode\Listblock;
use dokuwiki\Parsing\ParserMode\Preformatted;

class PreformattedTest extends ParserTestBase
{

    function testFile() {
        $this->P->addMode('file',new File());
        $this->P->parse('Foo <file>testing</file> Bar');
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo ']],
            ['p_close',[]],
            ['file',['testing',null,null]],
            ['p_open',[]],
            ['cdata',[' Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];

        $this->assertCalls($calls, $this->H->calls);
    }

    function testCode() {
        $this->P->addMode('code',new Code());
        $this->P->parse('Foo <code>testing</code> Bar');
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo ']],
            ['p_close',[]],
            ['code',['testing', null, null]],
            ['p_open',[]],
            ['cdata',[' Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testCodeWhitespace() {
        $this->P->addMode('code',new Code());
        $this->P->parse("Foo <code \n>testing</code> Bar");
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo ']],
            ['p_close',[]],
            ['code',['testing', null, null]],
            ['p_open',[]],
            ['cdata',[' Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testCodeLang() {
        $this->P->addMode('code',new Code());
        $this->P->parse("Foo <code php>testing</code> Bar");
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo ']],
            ['p_close',[]],
            ['code',['testing', 'php', null]],
            ['p_open',[]],
            ['cdata',[' Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testPreformatted() {
        $this->P->addMode('preformatted',new Preformatted());
        $this->P->parse("F  oo\n  x  \n    y  \nBar\n");
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\nF  oo"]],
            ['p_close',[]],
            ['preformatted',["x  \n  y  "]],
            ['p_open',[]],
            ['cdata',['Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testPreformattedWinEOL() {
        $this->P->addMode('preformatted',new Preformatted());
        $this->P->parse("F  oo\r\n  x  \r\n    y  \r\nBar\r\n");
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\nF  oo"]],
            ['p_close',[]],
            ['preformatted',["x  \n  y  "]],
            ['p_open',[]],
            ['cdata',['Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testPreformattedTab() {
        $this->P->addMode('preformatted',new Preformatted());
        $this->P->parse("F  oo\n\tx\t\n\t\ty\t\nBar\n");
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\nF  oo"]],
            ['p_close',[]],
            ['preformatted',["x\t\n\ty\t"]],
            ['p_open',[]],
            ['cdata',["Bar"]],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testPreformattedTabWinEOL() {
        $this->P->addMode('preformatted',new Preformatted());
        $this->P->parse("F  oo\r\n\tx\t\r\n\t\ty\t\r\nBar\r\n");
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\nF  oo"]],
            ['p_close',[]],
            ['preformatted',["x\t\n\ty\t"]],
            ['p_open',[]],
            ['cdata',["Bar"]],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testPreformattedList() {
        $this->P->addMode('preformatted',new Preformatted());
        $this->P->addMode('listblock',new Listblock());
        $this->P->parse("  - x \n  * y \nF  oo\n  x  \n    y  \n  -X\n  *Y\nBar\n");
        $calls = [
            ['document_start',[]],
            ['listo_open',[]],
            ['listitem_open',[1]],
            ['listcontent_open',[]],
            ['cdata',[" x "]],
            ['listcontent_close',[]],
            ['listitem_close',[]],
            ['listo_close',[]],
            ['listu_open',[]],
            ['listitem_open',[1]],
            ['listcontent_open',[]],
            ['cdata',[" y "]],
            ['listcontent_close',[]],
            ['listitem_close',[]],
            ['listu_close',[]],
            ['p_open',[]],
            ['cdata',["F  oo"]],
            ['p_close',[]],
            ['preformatted',["x  \n  y  \n-X\n*Y"]],
            ['p_open',[]],
            ['cdata',["Bar"]],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }


    function testPreformattedPlusHeaderAndEol() {
        // Note that EOL must come after preformatted!
        $this->P->addMode('preformatted',new Preformatted());
        $this->P->addMode('header',new Header());
        $this->P->addMode('eol',new Eol());
        $this->P->parse("F  oo\n  ==Test==\n    y  \nBar\n");
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["F  oo"]],
            ['p_close',[]],
            ['preformatted',["==Test==\n  y  "]],
            ['p_open',[]],
            ['cdata',['Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }
}
