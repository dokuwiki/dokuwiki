<?php
require_once 'parser.inc.php';

class TestOfDoku_Parser_Quotes extends TestOfDoku_Parser {

    function setup() {
        parent::setup();
        global $conf;
        $conf['typography'] = 2;
    }

    function testSingleQuoteOpening() {
        $this->P->addMode('quotes',new Doku_Parser_Mode_Quotes());
        $this->P->parse("Foo 'hello Bar");

        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'Foo ')),
            array('singlequoteopening',array()),
            array('cdata',array('hello Bar')),
            array('p_close',array()),
            array('document_end',array()),
        );

        $this->assertEquals(array_map('stripbyteindex',$this->H->calls),$calls);
    }

    function testSingleQuoteOpeningSpecial() {
        $this->P->addMode('quotes',new Doku_Parser_Mode_Quotes());
        $this->P->parse("Foo said:'hello Bar");

        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'Foo said:')),
            array('singlequoteopening',array()),
            array('cdata',array('hello Bar')),
            array('p_close',array()),
            array('document_end',array()),
        );

        $this->assertEquals(array_map('stripbyteindex',$this->H->calls),$calls);
    }

    function testSingleQuoteClosing() {
        $this->P->addMode('quotes',new Doku_Parser_Mode_Quotes());
        $this->P->parse("Foo hello' Bar");

        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'Foo hello')),
            array('singlequoteclosing',array()),
            array('cdata',array(' Bar')),
            array('p_close',array()),
            array('document_end',array()),
        );

        $this->assertEquals(array_map('stripbyteindex',$this->H->calls),$calls);
    }

    function testSingleQuoteClosingSpecial() {
        $this->P->addMode('quotes',new Doku_Parser_Mode_Quotes());
        $this->P->parse("Foo hello') Bar");

        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'Foo hello')),
            array('singlequoteclosing',array()),
            array('cdata',array(') Bar')),
            array('p_close',array()),
            array('document_end',array()),
        );

        $this->assertEquals(array_map('stripbyteindex',$this->H->calls),$calls);
    }

    function testSingleQuotes() {
        $this->P->addMode('quotes',new Doku_Parser_Mode_Quotes());
        $this->P->parse("Foo 'hello' Bar");

        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'Foo ')),
            array('singlequoteopening',array()),
            array('cdata',array('hello')),
            array('singlequoteclosing',array()),
            array('cdata',array(' Bar')),
            array('p_close',array()),
            array('document_end',array()),
        );

        $this->assertEquals(array_map('stripbyteindex',$this->H->calls),$calls);
    }

    function testApostrophe() {
        $this->P->addMode('quotes',new Doku_Parser_Mode_Quotes());
        $this->P->parse("hey it's fine weather today");

        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'hey it')),
            array('apostrophe',array()),
            array('cdata',array('s fine weather today')),
            array('p_close',array()),
            array('document_end',array()),
        );

        $this->assertEquals(array_map('stripbyteindex',$this->H->calls),$calls);
    }


    function testSingleQuotesSpecial() {
        $this->P->addMode('quotes',new Doku_Parser_Mode_Quotes());
        $this->P->parse("Foo ('hello') Bar");

        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'Foo (')),
            array('singlequoteopening',array()),
            array('cdata',array('hello')),
            array('singlequoteclosing',array()),
            array('cdata',array(') Bar')),
            array('p_close',array()),
            array('document_end',array()),
        );

        $this->assertEquals(array_map('stripbyteindex',$this->H->calls),$calls);
    }

    function testDoubleQuoteOpening() {
        $this->P->addMode('quotes',new Doku_Parser_Mode_Quotes());
        $this->P->parse('Foo "hello Bar');

        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'Foo ')),
            array('doublequoteopening',array()),
            array('cdata',array('hello Bar')),
            array('p_close',array()),
            array('document_end',array()),
        );

        $this->assertEquals(array_map('stripbyteindex',$this->H->calls),$calls);
    }

    function testDoubleQuoteOpeningSpecial() {
        $this->P->addMode('quotes',new Doku_Parser_Mode_Quotes());
        $this->P->parse('Foo said:"hello Bar');

        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'Foo said:')),
            array('doublequoteopening',array()),
            array('cdata',array('hello Bar')),
            array('p_close',array()),
            array('document_end',array()),
        );

        $this->assertEquals(array_map('stripbyteindex',$this->H->calls),$calls);
    }

    function testDoubleQuoteClosing() {
        $this->P->addMode('quotes',new Doku_Parser_Mode_Quotes());
        $this->P->parse('Foo hello" Bar');

        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'Foo hello')),
            array('doublequoteclosing',array()),
            array('cdata',array(' Bar')),
            array('p_close',array()),
            array('document_end',array()),
        );

        $this->assertEquals(array_map('stripbyteindex',$this->H->calls),$calls);
    }

    function testDoubleQuoteClosingSpecial() {
        $this->P->addMode('quotes',new Doku_Parser_Mode_Quotes());
        $this->P->parse('Foo hello") Bar');

        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'Foo hello')),
            array('doublequoteclosing',array()),
            array('cdata',array(') Bar')),
            array('p_close',array()),
            array('document_end',array()),
        );

        $this->assertEquals(array_map('stripbyteindex',$this->H->calls),$calls);
    }

    function testDoubleQuotes() {
        $this->P->addMode('quotes',new Doku_Parser_Mode_Quotes());
        $this->P->parse('Foo "hello" Bar');

        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'Foo ')),
            array('doublequoteopening',array()),
            array('cdata',array('hello')),
            array('doublequoteclosing',array()),
            array('cdata',array(' Bar')),
            array('p_close',array()),
            array('document_end',array()),
        );

        $this->assertEquals(array_map('stripbyteindex',$this->H->calls),$calls);
    }

    function testDoubleQuotesSpecial() {
        $this->P->addMode('quotes',new Doku_Parser_Mode_Quotes());
        $this->P->parse('Foo ("hello") Bar');

        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'Foo (')),
            array('doublequoteopening',array()),
            array('cdata',array('hello')),
            array('doublequoteclosing',array()),
            array('cdata',array(') Bar')),
            array('p_close',array()),
            array('document_end',array()),
        );

        $this->assertEquals(array_map('stripbyteindex',$this->H->calls),$calls);
    }

    function testAllQuotes() {
        $this->P->addMode('quotes',new Doku_Parser_Mode_Quotes());
        $this->P->parse('There was written "He thought \'It\'s a man\'s world\'".');

        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'There was written ')),
            array('doublequoteopening',array()),
            array('cdata',array('He thought ')),
            array('singlequoteopening',array()),
            array('cdata',array('It')),
            array('apostrophe',array()),
            array('cdata',array('s a man')),
            array('apostrophe',array()),
            array('cdata',array('s world')),
            array('singlequoteclosing',array()),
            array('doublequoteclosing',array()),
            array('cdata',array(".")),
            array('p_close',array()),
            array('document_end',array()),
        );

        $this->assertEquals(array_map('stripbyteindex',$this->H->calls),$calls);
    }

}

