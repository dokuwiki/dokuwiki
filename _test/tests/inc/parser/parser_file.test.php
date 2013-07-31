<?php
require_once 'parser.inc.php';

class TestOfDoku_Parser_File extends TestOfDoku_Parser {

    function setUp() {
        parent::setUp();
        $this->P->addMode('file',new Doku_Parser_Mode_File());
    }

    function testFile() {
        $this->P->parse('Foo <file>Test</file> Bar');
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'Foo ')),
            array('p_close',array()),
            array('file',array('Test',null,null)),
            array('p_open',array()),
            array('cdata',array(' Bar')),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEquals(array_map('stripbyteindex',$this->H->calls),$calls);
    }

}

