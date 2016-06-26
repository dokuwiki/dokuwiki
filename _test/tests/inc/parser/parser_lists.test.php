<?php
require_once 'parser.inc.php';

class TestOfDoku_Parser_Lists extends TestOfDoku_Parser {

    function testUnorderedList() {
        $this->P->addMode('listblock',new Doku_Parser_Mode_ListBlock());
        $this->P->parse('
  *A
    * B
  * C
');
        $calls = array (
            array('document_start',array()),
            array('listu_open',array()),
            array('listitem_open',array(1,Doku_Handler_List::NODE)),
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
        $this->P->addMode('listblock',new Doku_Parser_Mode_ListBlock());
        $this->P->parse('
  -A
    - B
  - C
');
        $calls = array (
            array('document_start',array()),
            array('listo_open',array()),
            array('listitem_open',array(1,Doku_Handler_List::NODE)),
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
        $this->P->addMode('listblock',new Doku_Parser_Mode_ListBlock());
        $this->P->parse('
  -A
    * B
  - C
');
        $calls = array (
            array('document_start',array()),
            array('listo_open',array()),
            array('listitem_open',array(1,Doku_Handler_List::NODE)),
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
        $this->P->addMode('listblock',new Doku_Parser_Mode_ListBlock());
        $this->P->parse("\r\n  *A\r\n    * B\r\n  * C\r\n");
        $calls = array (
            array('document_start',array()),
            array('listu_open',array()),
            array('listitem_open',array(1,Doku_Handler_List::NODE)),
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
        $this->P->addMode('listblock',new Doku_Parser_Mode_ListBlock());
        $this->P->parse("\r\n  -A\r\n    - B\r\n  - C\r\n");
        $calls = array (
            array('document_start',array()),
            array('listo_open',array()),
            array('listitem_open',array(1,Doku_Handler_List::NODE)),
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
        $this->P->addMode('listblock',new Doku_Parser_Mode_ListBlock());
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
        $this->P->addMode('listblock',new Doku_Parser_Mode_ListBlock());
        $this->P->addMode('eol',new Doku_Parser_Mode_Eol());
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
            array('listitem_open',array(1,Doku_Handler_List::NODE)),
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
        $this->P->addMode('listblock',new Doku_Parser_Mode_ListBlock());
        $this->P->addMode('strong',new Doku_Parser_Mode_Formatting('strong'));
        $this->P->parse('
  ***A**
    *** B
  * C**
');
        $calls = array (
            array('document_start',array()),
            array('listu_open',array()),
            array('listitem_open',array(1,Doku_Handler_List::NODE)),
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
        $this->P->addMode('listblock',new Doku_Parser_Mode_ListBlock());
        $this->P->addMode('unformatted',new Doku_Parser_Mode_Unformatted());
        $this->P->parse('
  *%%A%%
    *%% B
  * C%%
');
        $calls = array (
            array('document_start',array()),
            array('listu_open',array()),
            array('listitem_open',array(1,Doku_Handler_List::NODE)),
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
        $this->P->addMode('listblock',new Doku_Parser_Mode_ListBlock());
        $this->P->addMode('linebreak',new Doku_Parser_Mode_Linebreak());
        $this->P->parse('
  *A\\\\ D
    * B
  * C
');
        $calls = array (
            array('document_start',array()),
            array('listu_open',array()),
            array('listitem_open',array(1,Doku_Handler_List::NODE)),
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
        $this->P->addMode('listblock',new Doku_Parser_Mode_ListBlock());
        $this->P->addMode('linebreak',new Doku_Parser_Mode_Linebreak());
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
        $this->P->addMode('listblock',new Doku_Parser_Mode_ListBlock());
        $this->P->addMode('footnote',new Doku_Parser_Mode_Footnote());
        $this->P->parse('
  *((A))
    *(( B
  * C )) 

');
        $calls = array (
            array('document_start',array()),
            array('listu_open',array()),
            array('listitem_open',array(1,Doku_Handler_List::NODE)),
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

