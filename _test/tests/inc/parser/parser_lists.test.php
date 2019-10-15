<?php

use dokuwiki\Parsing\Handler\Lists;
use dokuwiki\Parsing\ParserMode\Eol;
use dokuwiki\Parsing\ParserMode\Footnote;
use dokuwiki\Parsing\ParserMode\Formatting;
use dokuwiki\Parsing\ParserMode\Linebreak;
use dokuwiki\Parsing\ParserMode\Listblock;
use dokuwiki\Parsing\ParserMode\Unformatted;

require_once 'parser.inc.php';

class TestOfDoku_Parser_Lists extends TestOfDoku_Parser {

    function testUnorderedList() {
        $this->P->addMode('listblock',new Listblock());
        $this->P->parse('
  *A
    * B
  * C
');
        $calls = array (
            array('document_start',array()),
            array('listu_open',array()),
            array('listitem_open',array(1,Lists::NODE)),
            array('listcontent_open',array()),
            array('cdata',array("A")),
            array('listcontent_close',array()),
            array('listu_open',array()),
            array('listitem_open',array(2)),
            array('listcontent_open',array()),
            array('cdata',array(' B')),
            array('listcontent_close',array()),
            array('listitem_close',array()),
            array('listu_close',array()),
            array('listitem_close',array()),
            array('listitem_open',array(1)),
            array('listcontent_open',array()),
            array('cdata',array(' C')),
            array('listcontent_close',array()),
            array('listitem_close',array()),
            array('listu_close',array()),
            array('document_end',array()),
        );
        $this->assertEquals(array_map('stripbyteindex',$this->H->calls),$calls);
    }

    function testOrderedList() {
        $this->P->addMode('listblock',new Listblock());
        $this->P->parse('
  -A
    - B
  - C
');
        $calls = array (
            array('document_start',array()),
            array('listo_open',array()),
            array('listitem_open',array(1,Lists::NODE)),
            array('listcontent_open',array()),
            array('cdata',array("A")),
            array('listcontent_close',array()),
            array('listo_open',array()),
            array('listitem_open',array(2)),
            array('listcontent_open',array()),
            array('cdata',array(' B')),
            array('listcontent_close',array()),
            array('listitem_close',array()),
            array('listo_close',array()),
            array('listitem_close',array()),
            array('listitem_open',array(1)),
            array('listcontent_open',array()),
            array('cdata',array(' C')),
            array('listcontent_close',array()),
            array('listitem_close',array()),
            array('listo_close',array()),
            array('document_end',array()),
        );
        $this->assertEquals(array_map('stripbyteindex',$this->H->calls),$calls);
    }


    function testMixedList() {
        $this->P->addMode('listblock',new Listblock());
        $this->P->parse('
  -A
    * B
  - C
');
        $calls = array (
            array('document_start',array()),
            array('listo_open',array()),
            array('listitem_open',array(1,Lists::NODE)),
            array('listcontent_open',array()),
            array('cdata',array("A")),
            array('listcontent_close',array()),
            array('listu_open',array()),
            array('listitem_open',array(2)),
            array('listcontent_open',array()),
            array('cdata',array(' B')),
            array('listcontent_close',array()),
            array('listitem_close',array()),
            array('listu_close',array()),
            array('listitem_close',array()),
            array('listitem_open',array(1)),
            array('listcontent_open',array()),
            array('cdata',array(' C')),
            array('listcontent_close',array()),
            array('listitem_close',array()),
            array('listo_close',array()),
            array('document_end',array()),
        );
        $this->assertEquals(array_map('stripbyteindex',$this->H->calls),$calls);
    }

    function testUnorderedListWinEOL() {
        $this->P->addMode('listblock',new Listblock());
        $this->P->parse("\r\n  *A\r\n    * B\r\n  * C\r\n");
        $calls = array (
            array('document_start',array()),
            array('listu_open',array()),
            array('listitem_open',array(1,Lists::NODE)),
            array('listcontent_open',array()),
            array('cdata',array("A")),
            array('listcontent_close',array()),
            array('listu_open',array()),
            array('listitem_open',array(2)),
            array('listcontent_open',array()),
            array('cdata',array(' B')),
            array('listcontent_close',array()),
            array('listitem_close',array()),
            array('listu_close',array()),
            array('listitem_close',array()),
            array('listitem_open',array(1)),
            array('listcontent_open',array()),
            array('cdata',array(' C')),
            array('listcontent_close',array()),
            array('listitem_close',array()),
            array('listu_close',array()),
            array('document_end',array()),
        );
        $this->assertEquals(array_map('stripbyteindex',$this->H->calls),$calls);
    }

    function testOrderedListWinEOL() {
        $this->P->addMode('listblock',new Listblock());
        $this->P->parse("\r\n  -A\r\n    - B\r\n  - C\r\n");
        $calls = array (
            array('document_start',array()),
            array('listo_open',array()),
            array('listitem_open',array(1,Lists::NODE)),
            array('listcontent_open',array()),
            array('cdata',array("A")),
            array('listcontent_close',array()),
            array('listo_open',array()),
            array('listitem_open',array(2)),
            array('listcontent_open',array()),
            array('cdata',array(' B')),
            array('listcontent_close',array()),
            array('listitem_close',array()),
            array('listo_close',array()),
            array('listitem_close',array()),
            array('listitem_open',array(1)),
            array('listcontent_open',array()),
            array('cdata',array(' C')),
            array('listcontent_close',array()),
            array('listitem_close',array()),
            array('listo_close',array()),
            array('document_end',array()),
        );
        $this->assertEquals(array_map('stripbyteindex',$this->H->calls),$calls);
    }

    function testNotAList() {
        $this->P->addMode('listblock',new Listblock());
        $this->P->parse("Foo  -bar  *foo Bar");
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\nFoo  -bar  *foo Bar")),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEquals(array_map('stripbyteindex',$this->H->calls),$calls);
    }

    function testUnorderedListParagraph() {
        $this->P->addMode('listblock',new Listblock());
        $this->P->addMode('eol',new Eol());
        $this->P->parse('Foo
  *A
    * B
  * C
Bar');
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("Foo")),
            array('p_close',array()),
            array('listu_open',array()),
            array('listitem_open',array(1,Lists::NODE)),
            array('listcontent_open',array()),
            array('cdata',array("A")),
            array('listcontent_close',array()),
            array('listu_open',array()),
            array('listitem_open',array(2)),
            array('listcontent_open',array()),
            array('cdata',array(' B')),
            array('listcontent_close',array()),
            array('listitem_close',array()),
            array('listu_close',array()),
            array('listitem_close',array()),
            array('listitem_open',array(1)),
            array('listcontent_open',array()),
            array('cdata',array(' C')),
            array('listcontent_close',array()),
            array('listitem_close',array()),
            array('listu_close',array()),
            array('p_open',array()),
            array('cdata',array("Bar")),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEquals(array_map('stripbyteindex',$this->H->calls),$calls);
    }

    // This is really a failing test - formatting able to spread across list items
    // Problem is fixing it would mean a major rewrite of lists
    function testUnorderedListStrong() {
        $this->P->addMode('listblock',new Listblock());
        $this->P->addMode('strong',new Formatting('strong'));
        $this->P->parse('
  ***A**
    *** B
  * C**
');
        $calls = array (
            array('document_start',array()),
            array('listu_open',array()),
            array('listitem_open',array(1,Lists::NODE)),
            array('listcontent_open',array()),
            array('strong_open',array()),
            array('cdata',array("A")),
            array('strong_close',array()),
            array('listcontent_close',array()),
            array('listu_open',array()),
            array('listitem_open',array(2)),
            array('listcontent_open',array()),
            array('strong_open',array()),
            array('cdata',array(" B\n  * C")),
            array('strong_close',array()),
            array('listcontent_close',array()),
            array('listitem_close',array()),
            array('listu_close',array()),
            array('listitem_close',array()),
            array('listu_close',array()),
            array('document_end',array()),
        );
        $this->assertEquals(array_map('stripbyteindex',$this->H->calls),$calls);
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
        $calls = array (
            array('document_start',array()),
            array('listu_open',array()),
            array('listitem_open',array(1,Lists::NODE)),
            array('listcontent_open',array()),
            array('unformatted',array("A")),
            array('listcontent_close',array()),
            array('listu_open',array()),
            array('listitem_open',array(2)),
            array('listcontent_open',array()),
            array('unformatted',array(" B\n  * C")),
            array('listcontent_close',array()),
            array('listitem_close',array()),
            array('listu_close',array()),
            array('listitem_close',array()),
            array('listu_close',array()),
            array('document_end',array()),
        );
        $this->assertEquals(array_map('stripbyteindex',$this->H->calls),$calls);
    }

    function testUnorderedListLinebreak() {
        $this->P->addMode('listblock',new Listblock());
        $this->P->addMode('linebreak',new Linebreak());
        $this->P->parse('
  *A\\\\ D
    * B
  * C
');
        $calls = array (
            array('document_start',array()),
            array('listu_open',array()),
            array('listitem_open',array(1,Lists::NODE)),
            array('listcontent_open',array()),
            array('cdata',array("A")),
            array('linebreak',array()),
            array('cdata',array("D")),
            array('listcontent_close',array()),
            array('listu_open',array()),
            array('listitem_open',array(2)),
            array('listcontent_open',array()),
            array('cdata',array(' B')),
            array('listcontent_close',array()),
            array('listitem_close',array()),
            array('listu_close',array()),
            array('listitem_close',array()),
            array('listitem_open',array(1)),
            array('listcontent_open',array()),
            array('cdata',array(' C')),
            array('listcontent_close',array()),
            array('listitem_close',array()),
            array('listu_close',array()),
            array('document_end',array()),
        );
        $this->assertEquals(array_map('stripbyteindex',$this->H->calls),$calls);
    }

    function testUnorderedListLinebreak2() {
        $this->P->addMode('listblock',new Listblock());
        $this->P->addMode('linebreak',new Linebreak());
        $this->P->parse('
  *A\\\\
  * B
');
        $calls = array (
            array('document_start',array()),
            array('listu_open',array()),
            array('listitem_open',array(1)),
            array('listcontent_open',array()),
            array('cdata',array("A")),
            array('linebreak',array()),
            array('listcontent_close',array()),
            array('listitem_close',array()),
            array('listitem_open',array(1)),
            array('listcontent_open',array()),
            array('cdata',array(' B')),
            array('listcontent_close',array()),
            array('listitem_close',array()),
            array('listu_close',array()),
            array('document_end',array()),
        );
        $this->assertEquals(array_map('stripbyteindex',$this->H->calls),$calls);
    }

    function testUnorderedListFootnote() {
        $this->P->addMode('listblock',new Listblock());
        $this->P->addMode('footnote',new Footnote());
        $this->P->parse('
  *((A))
    *(( B
  * C )) 

');
        $calls = array (
            array('document_start',array()),
            array('listu_open',array()),
            array('listitem_open',array(1,Lists::NODE)),
            array('listcontent_open',array()),
            array('nest', array( array(
                array('footnote_open',array()),
                array('cdata',array("A")),
                array('footnote_close',array())
            ))),
            array('listcontent_close',array()),
            array('listu_open',array()),
            array('listitem_open',array(2)),
            array('listcontent_open',array()),
            array('nest', array( array(
                array('footnote_open',array()),
                array('cdata',array(" B")),
                array('listu_open',array()),
                array('listitem_open',array(1)),
                array('listcontent_open',array()),
                array('cdata',array(" C )) ")),
                array('listcontent_close',array()),
                array('listitem_close',array()),
                array('listu_close',array()),
                array('cdata',array("\n\n")),
                array('footnote_close',array())
            ))),
            array('listcontent_close',array()),
            array('listitem_close',array()),
            array('listu_close',array()),
            array('listitem_close',array()),
            array('listu_close',array()),
            array('document_end',array())
        );

        $this->assertEquals(array_map('stripbyteindex',$this->H->calls),$calls);
    }
}

