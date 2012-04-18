<?php
require_once 'parser.inc.php';

class TestOfDoku_Parser_Preformatted extends TestOfDoku_Parser {

    function testFile() {
        $this->P->addMode('file',new Doku_Parser_Mode_File());
        $this->P->parse('Foo <file>testing</file> Bar');
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'Foo ')),
            array('p_close',array()),
            array('file',array('testing',null,null)),
            array('p_open',array()),
            array('cdata',array(' Bar')),
            array('p_close',array()),
            array('document_end',array()),
        );

        $this->assertEquals(array_map('stripbyteindex',$this->H->calls),$calls);
    }

    function testCode() {
        $this->P->addMode('code',new Doku_Parser_Mode_Code());
        $this->P->parse('Foo <code>testing</code> Bar');
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'Foo ')),
            array('p_close',array()),
            array('code',array('testing', null, null)),
            array('p_open',array()),
            array('cdata',array(' Bar')),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEquals(array_map('stripbyteindex',$this->H->calls),$calls);
    }

    function testCodeWhitespace() {
        $this->P->addMode('code',new Doku_Parser_Mode_Code());
        $this->P->parse("Foo <code \n>testing</code> Bar");
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'Foo ')),
            array('p_close',array()),
            array('code',array('testing', null, null)),
            array('p_open',array()),
            array('cdata',array(' Bar')),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEquals(array_map('stripbyteindex',$this->H->calls),$calls);
    }

    function testCodeLang() {
        $this->P->addMode('code',new Doku_Parser_Mode_Code());
        $this->P->parse("Foo <code php>testing</code> Bar");
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'Foo ')),
            array('p_close',array()),
            array('code',array('testing', 'php', null)),
            array('p_open',array()),
            array('cdata',array(' Bar')),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEquals(array_map('stripbyteindex',$this->H->calls),$calls);
    }

    function testPreformatted() {
        $this->P->addMode('preformatted',new Doku_Parser_Mode_Preformatted());
        $this->P->parse("F  oo\n  x  \n    y  \nBar\n");
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\nF  oo")),
            array('p_close',array()),
            array('preformatted',array("x  \n  y  ")),
            array('p_open',array()),
            array('cdata',array('Bar')),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEquals(array_map('stripbyteindex',$this->H->calls),$calls);
    }

    function testPreformattedWinEOL() {
        $this->P->addMode('preformatted',new Doku_Parser_Mode_Preformatted());
        $this->P->parse("F  oo\r\n  x  \r\n    y  \r\nBar\r\n");
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\nF  oo")),
            array('p_close',array()),
            array('preformatted',array("x  \n  y  ")),
            array('p_open',array()),
            array('cdata',array('Bar')),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEquals(array_map('stripbyteindex',$this->H->calls),$calls);
    }

    function testPreformattedTab() {
        $this->P->addMode('preformatted',new Doku_Parser_Mode_Preformatted());
        $this->P->parse("F  oo\n\tx\t\n\t\ty\t\nBar\n");
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\nF  oo")),
            array('p_close',array()),
            array('preformatted',array("x\t\n\ty\t")),
            array('p_open',array()),
            array('cdata',array("Bar")),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEquals(array_map('stripbyteindex',$this->H->calls),$calls);
    }

    function testPreformattedTabWinEOL() {
        $this->P->addMode('preformatted',new Doku_Parser_Mode_Preformatted());
        $this->P->parse("F  oo\r\n\tx\t\r\n\t\ty\t\r\nBar\r\n");
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\nF  oo")),
            array('p_close',array()),
            array('preformatted',array("x\t\n\ty\t")),
            array('p_open',array()),
            array('cdata',array("Bar")),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEquals(array_map('stripbyteindex',$this->H->calls),$calls);
    }

    function testPreformattedList() {
        $this->P->addMode('preformatted',new Doku_Parser_Mode_Preformatted());
        $this->P->addMode('listblock',new Doku_Parser_Mode_ListBlock());
        $this->P->parse("  - x \n  * y \nF  oo\n  x  \n    y  \n  -X\n  *Y\nBar\n");
        $calls = array (
            array('document_start',array()),
            array('listo_open',array()),
            array('listitem_open',array(1)),
            array('listcontent_open',array()),
            array('cdata',array(" x ")),
            array('listcontent_close',array()),
            array('listitem_close',array()),
            array('listo_close',array()),
            array('listu_open',array()),
            array('listitem_open',array(1)),
            array('listcontent_open',array()),
            array('cdata',array(" y ")),
            array('listcontent_close',array()),
            array('listitem_close',array()),
            array('listu_close',array()),
            array('p_open',array()),
            array('cdata',array("F  oo")),
            array('p_close',array()),
            array('preformatted',array("x  \n  y  \n-X\n*Y")),
            array('p_open',array()),
            array('cdata',array("Bar")),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEquals(array_map('stripbyteindex',$this->H->calls),$calls);
    }

    // test for php
    function testPHP() {

        $this->P->addMode('php',new Doku_Parser_Mode_PHP());
        $this->P->parse('Foo <php>testing</php> Bar');
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'Foo ')),
            array('php',array('testing')),
            array('cdata',array(' Bar')),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEquals(array_map('stripbyteindex',$this->H->calls),$calls);
    }

    // test with for HTML
    function testHTML() {

        $this->P->addMode('html',new Doku_Parser_Mode_HTML());
        $this->P->parse('Foo <html>testing</html> Bar');
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'Foo ')),
            array('html',array('testing')),
            array('cdata',array(' Bar')),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEquals(array_map('stripbyteindex',$this->H->calls),$calls);
    }



    function testPreformattedPlusHeaderAndEol() {
        // Note that EOL must come after preformatted!
        $this->P->addMode('preformatted',new Doku_Parser_Mode_Preformatted());
        $this->P->addMode('header',new Doku_Parser_Mode_Header());
        $this->P->addMode('eol',new Doku_Parser_Mode_Eol());
        $this->P->parse("F  oo\n  ==Test==\n    y  \nBar\n");
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("F  oo")),
            array('p_close',array()),
            array('preformatted',array("==Test==\n  y  ")),
            array('p_open',array()),
            array('cdata',array('Bar')),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEquals(array_map('stripbyteindex',$this->H->calls),$calls);
    }
}

