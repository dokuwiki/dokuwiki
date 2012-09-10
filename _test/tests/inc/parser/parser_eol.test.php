<?php
require_once 'parser.inc.php';

class TestOfDoku_Parser_Eol extends TestOfDoku_Parser {

    function testEol() {
        $this->P->addMode('eol',new Doku_Parser_Mode_Eol());
        $this->P->parse("Foo\nBar");
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("Foo".DOKU_PARSER_EOL."Bar")),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEquals(array_map('stripByteIndex',$this->H->calls),$calls);
    }

    function testEolMultiple() {
        $this->P->addMode('eol',new Doku_Parser_Mode_Eol());
        $this->P->parse("Foo\n\nbar\nFoo");
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("Foo")),
            array('p_close',array()),
            array('p_open',array()),
            array('cdata',array("bar".DOKU_PARSER_EOL."Foo")),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEquals(array_map('stripByteIndex',$this->H->calls),$calls);
    }

    function testWinEol() {
        $this->P->addMode('eol',new Doku_Parser_Mode_Eol());
        $this->P->parse("Foo\r\nBar");
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("Foo".DOKU_PARSER_EOL."Bar")),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEquals(array_map('stripByteIndex',$this->H->calls),$calls);
    }

    function testLinebreak() {
        $this->P->addMode('linebreak',new Doku_Parser_Mode_Linebreak());
        $this->P->parse('Foo\\\\ Bar');
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\nFoo")),
            array('linebreak',array()),
            array('cdata',array("Bar")),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEquals(array_map('stripByteIndex',$this->H->calls),$calls);
    }

    function testLinebreakPlusEol() {
        $this->P->addMode('linebreak',new Doku_Parser_Mode_Linebreak());
        $this->P->addMode('eol',new Doku_Parser_Mode_Eol());
        $this->P->parse('Foo\\\\'."\n\n".'Bar');

        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("Foo")),
            array('linebreak',array()),
            array('p_close',array()),
            array('p_open',array()),
            array('cdata',array("Bar")),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEquals(array_map('stripByteIndex',$this->H->calls),$calls);
    }

    function testLinebreakInvalid() {
        $this->P->addMode('linebreak',new Doku_Parser_Mode_Linebreak());
        $this->P->parse('Foo\\\\Bar');
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'Foo\\\\Bar')),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEquals(array_map('stripByteIndex',$this->H->calls),$calls);
    }

}

