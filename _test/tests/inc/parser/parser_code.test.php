<?php
require_once 'parser.inc.php';

class TestOfDoku_Parser_Code extends TestOfDoku_Parser {

    function setUp() {
        parent::setUp();
        $this->P->addMode('code',new Doku_Parser_Mode_Code());
    }

    function testCode() {
        $this->P->parse('Foo <code>Test</code> Bar');
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'Foo ')),
            array('p_close',array()),
            array('code',array('Test',null,null)),
            array('p_open',array()),
            array('cdata',array(' Bar')),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEquals(array_map('stripbyteindex',$this->H->calls),$calls);
    }

    function testCodeBash() {
        $this->P->parse('Foo <code bash>Test</code> Bar');
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'Foo ')),
            array('p_close',array()),
            array('code',array('Test','bash',null)),
            array('p_open',array()),
            array('cdata',array(' Bar')),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEquals(array_map('stripbyteindex',$this->H->calls),$calls);
    }

    function testCodeDownload() {
        $this->P->parse('Foo <code bash script.sh>Test</code> Bar');
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'Foo ')),
            array('p_close',array()),
            array('code',array('Test','bash','script.sh')),
            array('p_open',array()),
            array('cdata',array(' Bar')),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEquals(array_map('stripbyteindex',$this->H->calls),$calls);
    }

    function testCodeToken() {
        $this->P->parse('Foo <code2>Bar</code2><code>Test</code>');
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'Foo <code2>Bar</code2>')),
            array('p_close',array()),
            array('code',array('Test',null,null)),
            array('document_end',array()),
        );
        $this->assertEquals(array_map('stripbyteindex',$this->H->calls),$calls);
    }
}

