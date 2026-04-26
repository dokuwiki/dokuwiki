<?php

namespace dokuwiki\test\Parsing\ParserMode;

use dokuwiki\Parsing\Handler\Lists;
use dokuwiki\Parsing\ParserMode\Eol;
use dokuwiki\Parsing\ParserMode\Footnote;
use dokuwiki\Parsing\ParserMode\Strong;
use dokuwiki\Parsing\ParserMode\Linebreak;
use dokuwiki\Parsing\ParserMode\Listblock;
use dokuwiki\Parsing\ParserMode\Unformatted;

class ListsTest extends ParserTestBase
{

    function testUnorderedList() {
        $this->P->addMode('listblock',new Listblock());
        $this->P->parse('
  *A
    * B
  * C
');
        $calls = [
            ['document_start',[]],
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
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testOrderedList() {
        $this->P->addMode('listblock',new Listblock());
        $this->P->parse('
  -A
    - B
  - C
');
        $calls = [
            ['document_start',[]],
            ['listo_open',[]],
            ['listitem_open',[1,Lists::NODE]],
            ['listcontent_open',[]],
            ['cdata',["A"]],
            ['listcontent_close',[]],
            ['listo_open',[]],
            ['listitem_open',[2]],
            ['listcontent_open',[]],
            ['cdata',[' B']],
            ['listcontent_close',[]],
            ['listitem_close',[]],
            ['listo_close',[]],
            ['listitem_close',[]],
            ['listitem_open',[1]],
            ['listcontent_open',[]],
            ['cdata',[' C']],
            ['listcontent_close',[]],
            ['listitem_close',[]],
            ['listo_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }


    function testMixedList() {
        $this->P->addMode('listblock',new Listblock());
        $this->P->parse('
  -A
    * B
  - C
');
        $calls = [
            ['document_start',[]],
            ['listo_open',[]],
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
            ['listo_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testUnorderedListWinEOL() {
        $this->P->addMode('listblock',new Listblock());
        $this->P->parse("\r\n  *A\r\n    * B\r\n  * C\r\n");
        $calls = [
            ['document_start',[]],
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
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testOrderedListWinEOL() {
        $this->P->addMode('listblock',new Listblock());
        $this->P->parse("\r\n  -A\r\n    - B\r\n  - C\r\n");
        $calls = [
            ['document_start',[]],
            ['listo_open',[]],
            ['listitem_open',[1,Lists::NODE]],
            ['listcontent_open',[]],
            ['cdata',["A"]],
            ['listcontent_close',[]],
            ['listo_open',[]],
            ['listitem_open',[2]],
            ['listcontent_open',[]],
            ['cdata',[' B']],
            ['listcontent_close',[]],
            ['listitem_close',[]],
            ['listo_close',[]],
            ['listitem_close',[]],
            ['listitem_open',[1]],
            ['listcontent_open',[]],
            ['cdata',[' C']],
            ['listcontent_close',[]],
            ['listitem_close',[]],
            ['listo_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testNotAList() {
        $this->P->addMode('listblock',new Listblock());
        $this->P->parse("Foo  -bar  *foo Bar");
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\nFoo  -bar  *foo Bar"]],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testUnorderedListParagraph() {
        $this->P->addMode('listblock',new Listblock());
        $this->P->addMode('eol',new Eol());
        $this->P->parse('Foo
  *A
    * B
  * C
Bar');
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["Foo"]],
            ['p_close',[]],
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
            ['p_open',[]],
            ['cdata',["Bar"]],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    // This is really a failing test - formatting able to spread across list items
    // Problem is fixing it would mean a major rewrite of lists
    function testUnorderedListStrong() {
        $this->P->addMode('listblock',new Listblock());
        $this->P->addMode('strong',new Strong());
        $this->P->parse('
  ***A**
    *** B
  * C**
');
        $calls = [
            ['document_start',[]],
            ['listu_open',[]],
            ['listitem_open',[1,Lists::NODE]],
            ['listcontent_open',[]],
            ['strong_open',[]],
            ['cdata',["A"]],
            ['strong_close',[]],
            ['listcontent_close',[]],
            ['listu_open',[]],
            ['listitem_open',[2]],
            ['listcontent_open',[]],
            ['strong_open',[]],
            ['cdata',[" B\n  * C"]],
            ['strong_close',[]],
            ['listcontent_close',[]],
            ['listitem_close',[]],
            ['listu_close',[]],
            ['listitem_close',[]],
            ['listu_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    // This is really a failing test - unformatted able to spread across list items
    // Problem is fixing it would mean a major rewrite of lists
    function testUnorderedListUnformatted() {
        $this->P->addMode('listblock',new Listblock());
        $this->P->addMode('unformatted',new Unformatted());
        $this->P->parse('
  *%%A%%
    *%% B
  * C%%
');
        $calls = [
            ['document_start',[]],
            ['listu_open',[]],
            ['listitem_open',[1,Lists::NODE]],
            ['listcontent_open',[]],
            ['unformatted',["A"]],
            ['listcontent_close',[]],
            ['listu_open',[]],
            ['listitem_open',[2]],
            ['listcontent_open',[]],
            ['unformatted',[" B\n  * C"]],
            ['listcontent_close',[]],
            ['listitem_close',[]],
            ['listu_close',[]],
            ['listitem_close',[]],
            ['listu_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testUnorderedListLinebreak() {
        $this->P->addMode('listblock',new Listblock());
        $this->P->addMode('linebreak',new Linebreak());
        $this->P->parse('
  *A\\\\ D
    * B
  * C
');
        $calls = [
            ['document_start',[]],
            ['listu_open',[]],
            ['listitem_open',[1,Lists::NODE]],
            ['listcontent_open',[]],
            ['cdata',["A"]],
            ['linebreak',[]],
            ['cdata',["D"]],
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
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testUnorderedListLinebreak2() {
        $this->P->addMode('listblock',new Listblock());
        $this->P->addMode('linebreak',new Linebreak());
        $this->P->parse('
  *A\\\\
  * B
');
        $calls = [
            ['document_start',[]],
            ['listu_open',[]],
            ['listitem_open',[1]],
            ['listcontent_open',[]],
            ['cdata',["A"]],
            ['linebreak',[]],
            ['listcontent_close',[]],
            ['listitem_close',[]],
            ['listitem_open',[1]],
            ['listcontent_open',[]],
            ['cdata',[' B']],
            ['listcontent_close',[]],
            ['listitem_close',[]],
            ['listu_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testUnorderedListFootnote() {
        $this->P->addMode('listblock',new Listblock());
        $this->P->addMode('footnote',new Footnote());
        $this->P->parse("\n  *((A))\n    *(( B\n  * C )) \n\n");
        $calls = [
            ['document_start',[]],
            ['listu_open',[]],
            ['listitem_open',[1,Lists::NODE]],
            ['listcontent_open',[]],
            ['nest', [ [
                ['footnote_open',[]],
                ['cdata',["A"]],
                ['footnote_close',[]]
            ]]],
            ['listcontent_close',[]],
            ['listu_open',[]],
            ['listitem_open',[2]],
            ['listcontent_open',[]],
            ['nest', [ [
                ['footnote_open',[]],
                ['cdata',[" B"]],
                ['listu_open',[]],
                ['listitem_open',[1]],
                ['listcontent_open',[]],
                ['cdata',[" C )) "]],
                ['listcontent_close',[]],
                ['listitem_close',[]],
                ['listu_close',[]],
                ['cdata',["\n\n"]],
                ['footnote_close',[]]
            ]]],
            ['listcontent_close',[]],
            ['listitem_close',[]],
            ['listu_close',[]],
            ['listitem_close',[]],
            ['listu_close',[]],
            ['document_end',[]]
        ];

        $this->assertCalls($calls, $this->H->calls);
    }
}
