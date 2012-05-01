<?php
require_once 'parser.inc.php';

class TestOfDoku_Parser_Unformatted extends TestOfDoku_Parser {

    function testNowiki() {
        $this->P->addMode('unformatted',new Doku_Parser_Mode_Unformatted());
        $this->P->parse("Foo <nowiki>testing</nowiki> Bar");
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'Foo ')),
            array('unformatted',array('testing')),
            array('cdata',array(' Bar')),
            array('p_close',array()),
            array('document_end',array()),
        );

        $this->assertEquals(array_map('stripByteIndex',$this->H->calls),$calls);

    }

    function testDoublePercent() {
        $this->P->addMode('unformatted',new Doku_Parser_Mode_Unformatted());
        $this->P->parse("Foo %%testing%% Bar");
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'Foo ')),
            array('unformatted',array('testing')),
            array('cdata',array(' Bar')),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEquals(array_map('stripByteIndex',$this->H->calls),$calls);
    }
}

