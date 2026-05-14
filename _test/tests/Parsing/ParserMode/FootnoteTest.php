<?php

namespace dokuwiki\test\Parsing\ParserMode;

use dokuwiki\Parsing\Handler\Lists;
use dokuwiki\Parsing\ParserMode\Code;
use dokuwiki\Parsing\ParserMode\Eol;
use dokuwiki\Parsing\ParserMode\Footnote;
use dokuwiki\Parsing\ParserMode\Strong;
use dokuwiki\Parsing\ParserMode\Hr;
use dokuwiki\Parsing\ParserMode\Listblock;
use dokuwiki\Parsing\ParserMode\Preformatted;
use dokuwiki\Parsing\ParserMode\Quote;
use dokuwiki\Parsing\ParserMode\Table;
use dokuwiki\Parsing\ParserMode\Unformatted;

class FootnoteTest extends ParserTestBase
{

    function setUp() : void {
        parent::setUp();
        $this->P->addMode('footnote',new Footnote());
    }

    function testFootnote() {
        $this->P->parse('Foo (( testing )) Bar');
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo ']],
            ['nest', [ [
              ['footnote_open',[]],
              ['cdata',[' testing ']],
              ['footnote_close',[]],
            ]]],
            ['cdata',[' Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testNotAFootnote() {
        $this->P->parse("Foo (( testing\n Bar");
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\nFoo (( testing\n Bar"]],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testFootnoteLinefeed() {
        $this->P->addMode('eol',new Eol());
        $this->P->parse("Foo (( testing\ntesting )) Bar");
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',['Foo ']],
            ['nest', [ [
              ['footnote_open',[]],
              ['cdata',[" testing\ntesting "]],
              ['footnote_close',[]],
            ]]],
            ['cdata',[' Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testFootnoteNested() {
        $this->P->parse('Foo (( x((y))z )) Bar');
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo ']],
            ['nest', [ [
              ['footnote_open',[]],
              ['cdata',[' x((y']],
              ['footnote_close',[]],
            ]]],
            ['cdata',['z )) Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testFootnoteEol() {
        $this->P->addMode('eol',new Eol());
        $this->P->parse("Foo \nX(( test\ning ))Y\n Bar");
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',['Foo '."\n".'X']],
            ['nest', [ [
              ['footnote_open',[]],
              ['cdata',[" test\ning "]],
              ['footnote_close',[]],
            ]]],
            ['cdata',['Y'."\n".' Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testFootnoteStrong() {
        $this->P->addMode('strong',new Strong());
        $this->P->parse('Foo (( **testing** )) Bar');
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo ']],
            ['nest', [ [
              ['footnote_open',[]],
              ['cdata',[' ']],
              ['strong_open',[]],
              ['cdata',['testing']],
              ['strong_close',[]],
              ['cdata',[' ']],
              ['footnote_close',[]],
            ]]],
            ['cdata',[' Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testFootnoteHr() {
        $this->P->addMode('hr',new Hr());
        $this->P->parse("Foo (( \n ---- \n )) Bar");
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo ']],
            ['nest', [ [
              ['footnote_open',[]],
              ['cdata',[' ']],
              ['hr',[]],
              ['cdata',["\n "]],
              ['footnote_close',[]],
            ]]],
            ['cdata',[' Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testFootnoteCode() {
        $this->P->addMode('code',new Code());
        $this->P->parse("Foo (( <code>Test</code> )) Bar");
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo ']],
            ['nest', [ [
              ['footnote_open',[]],
              ['cdata',[' ']],
              ['code',['Test',null,null]],
              ['cdata',[' ']],
              ['footnote_close',[]],
            ]]],
            ['cdata',[' Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testFootnotePreformatted() {
        $this->P->addMode('preformatted',new Preformatted());
        $this->P->parse("Foo (( \n  Test\n )) Bar");
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo ']],
            ['nest', [ [
              ['footnote_open',[]],
              ['cdata',[' ']],
              ['preformatted',['Test']],
              ['cdata',[' ']],
              ['footnote_close',[]],
            ]]],
            ['cdata',[' Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testFootnotePreformattedEol() {
        $this->P->addMode('preformatted',new Preformatted());
        $this->P->addMode('eol',new Eol());
        $this->P->parse("Foo (( \n  Test\n )) Bar");
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',['Foo ']],
            ['nest', [ [
              ['footnote_open',[]],
              ['cdata',[' ']],
              ['preformatted',['Test']],
              ['cdata',[' ']],
              ['footnote_close',[]],
            ]]],
            ['cdata',[' Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];

        $this->assertCalls($calls, $this->H->calls);
    }

    function testFootnoteUnformatted() {
        $this->P->addMode('unformatted',new Unformatted());
        $this->P->parse("Foo (( <nowiki>Test</nowiki> )) Bar");
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo ']],
            ['nest', [ [
              ['footnote_open',[]],
              ['cdata',[' ']],
              ['unformatted',['Test']],
              ['cdata',[' ']],
              ['footnote_close',[]],
            ]]],
            ['cdata',[' Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testFootnoteNotHeader() {
        $this->P->addMode('unformatted',new Unformatted());
        $this->P->parse("Foo (( \n====Test====\n )) Bar");
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo ']],
            ['nest', [ [
              ['footnote_open',[]],
              ['cdata',[" \n====Test====\n "]],
              ['footnote_close',[]],
            ]]],
            ['cdata',[' Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testFootnoteTable() {
        $this->P->addMode('table',new Table());
        $this->P->parse("Foo ((
| Row 0 Col 1    | Row 0 Col 2     | Row 0 Col 3        |
| Row 1 Col 1    | Row 1 Col 2     | Row 1 Col 3        |
 )) Bar");
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo ']],
            ['nest', [ [
              ['footnote_open',[]],
              ['table_open',[3, 2, 8]],
              ['tablerow_open',[]],
              ['tablecell_open',[1,'left',1]],
              ['cdata',[' Row 0 Col 1    ']],
              ['tablecell_close',[]],
              ['tablecell_open',[1,'left',1]],
              ['cdata',[' Row 0 Col 2     ']],
              ['tablecell_close',[]],
              ['tablecell_open',[1,'left',1]],
              ['cdata',[' Row 0 Col 3        ']],
              ['tablecell_close',[]],
              ['tablerow_close',[]],
              ['tablerow_open',[]],
              ['tablecell_open',[1,'left',1]],
              ['cdata',[' Row 1 Col 1    ']],
              ['tablecell_close',[]],
              ['tablecell_open',[1,'left',1]],
              ['cdata',[' Row 1 Col 2     ']],
              ['tablecell_close',[]],
              ['tablecell_open',[1,'left',1]],
              ['cdata',[' Row 1 Col 3        ']],
              ['tablecell_close',[]],
              ['tablerow_close',[]],
              ['table_close',[123]],
              ['cdata',[' ']],
              ['footnote_close',[]],
            ]]],
            ['cdata',[' Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testFootnoteList() {
        $this->P->addMode('listblock',new ListBlock());
        $this->P->parse("Foo ((
  *A
    * B
  * C
 )) Bar");
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo ']],
            ['nest', [ [
              ['footnote_open',[]],
              ['listu_open',[]],
              ['listitem_open',[1,Lists::NODE]],
              ['listcontent_open',[]],
              ['cdata',["A"]],
              ['listcontent_close',[]],
              ['listu_open',[]],
              ['listitem_open',[2]],
              ['listcontent_open',[]],
              ['cdata',[' B']],
              ['listcontent_close',[]],
              ['listitem_close',[]],
              ['listu_close',[]],
              ['listitem_close',[]],
              ['listitem_open',[1]],
              ['listcontent_open',[]],
              ['cdata',[' C']],
              ['listcontent_close',[]],
              ['listitem_close',[]],
              ['listu_close',[]],
              ['cdata',[' ']],
              ['footnote_close',[]],
            ]]],
            ['cdata',[' Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testFootnoteQuote() {
        $this->P->addMode('quote',new Quote());
        $this->P->parse("Foo ((
> def
>>ghi
 )) Bar");
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo ']],
            ['nest', [ [
              ['footnote_open',[]],
              ['quote_open',[]],
              ['cdata',[" def"]],
              ['quote_open',[]],
              ['cdata',["ghi"]],
              ['quote_close',[]],
              ['quote_close',[]],
              ['cdata',[' ']],
              ['footnote_close',[]],
            ]]],
            ['cdata',[' Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];

        $this->assertCalls($calls, $this->H->calls);
    }

    function testFootnoteNesting() {
        $this->P->addMode('strong',new Strong());
        $this->P->parse("(( a ** (( b )) ** c ))");

        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n"]],
            ['nest', [ [
              ['footnote_open',[]],
              ['cdata',[' a ']],
              ['strong_open',[]],
              ['cdata',[' (( b ']],
              ['footnote_close',[]],
            ]]],
            ['cdata',[" "]],
            ['strong_close',[]],
            ['cdata',[" c ))"]],
            ['p_close',[]],
            ['document_end',[]],
        ];

        $this->assertCalls($calls, $this->H->calls);
    }
}
