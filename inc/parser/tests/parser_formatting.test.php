<?php
require_once 'parser.test.php';

class TestOfDoku_Parser_Formatting extends TestOfDoku_Parser {

    function TestOfDoku_Parser_Formatting() {
        $this->UnitTestCase('TestOfDoku_Parser_Formatting');
    }
    
    function testStrong() {
        $this->P->addMode('strong',new Doku_Parser_Mode_Formatting('strong'));
        $this->P->parse('abc **bar** def');
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'abc ')),
            array('strong_open',array()),
            array('cdata',array('bar')),
            array('strong_close',array()),
            array('cdata',array(' def'."\n")),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEqual(array_map('stripbyteindex',$this->H->calls),$calls);
    }
    
    function testNotStrong() {
        $this->P->addMode('strong',new Doku_Parser_Mode_Formatting('strong'));
        $this->P->parse('abc **bar def');
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\nabc **bar def\n")),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEqual(array_map('stripbyteindex',$this->H->calls),$calls);
    }

    function testEm() {
        $this->P->addMode('emphasis',new Doku_Parser_Mode_Formatting('emphasis'));
        $this->P->parse('abc //bar// def');
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'abc ')),
            array('emphasis_open',array()),
            array('cdata',array('bar')),
            array('emphasis_close',array()),
            array('cdata',array(' def'."\n")),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEqual(array_map('stripbyteindex',$this->H->calls),$calls);
    }
    
    function testNotEm() {
        $this->P->addMode('emphasis',new Doku_Parser_Mode_Formatting('emphasis'));
        $this->P->parse('abc //bar def');
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\nabc //bar def\n")),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEqual(array_map('stripbyteindex',$this->H->calls),$calls);
    }

    function testUnderline() {
        $this->P->addMode('underline',new Doku_Parser_Mode_Formatting('underline'));
        $this->P->parse('abc __bar__ def');
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'abc ')),
            array('underline_open',array()),
            array('cdata',array('bar')),
            array('underline_close',array()),
            array('cdata',array(' def'."\n")),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEqual(array_map('stripbyteindex',$this->H->calls),$calls);
    }

    function testNotUnderline() {
        $this->P->addMode('underline',new Doku_Parser_Mode_Formatting('underline'));
        $this->P->parse('abc __bar def');
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\nabc __bar def\n")),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEqual(array_map('stripbyteindex',$this->H->calls),$calls);
    }

    function testMonospace() {
        $this->P->addMode('monospace',new Doku_Parser_Mode_Formatting('monospace'));
        $this->P->parse("abc ''bar'' def");
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'abc ')),
            array('monospace_open',array()),
            array('cdata',array('bar')),
            array('monospace_close',array()),
            array('cdata',array(' def'."\n")),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEqual(array_map('stripbyteindex',$this->H->calls),$calls);
    }
    
    function testNotMonospace() {
        $this->P->addMode('monospace',new Doku_Parser_Mode_Formatting('monospace'));
        $this->P->parse("abc ''bar def");
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\nabc ''bar def\n")),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEqual(array_map('stripbyteindex',$this->H->calls),$calls);
    }

    function testSubscript() {
        $this->P->addMode('subscript',new Doku_Parser_Mode_Formatting('subscript'));
        $this->P->parse('abc <sub>bar</sub> def');
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'abc ')),
            array('subscript_open',array()),
            array('cdata',array('bar')),
            array('subscript_close',array()),
            array('cdata',array(' def'."\n")),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEqual(array_map('stripbyteindex',$this->H->calls),$calls);
    }
    
    function testNotSubscript() {
        $this->P->addMode('subscript',new Doku_Parser_Mode_Formatting('subscript'));
        $this->P->parse('abc <sub>bar def');
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\nabc <sub>bar def\n")),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEqual(array_map('stripbyteindex',$this->H->calls),$calls);
    }
    
    function testSuperscript() {
        $this->P->addMode('superscript',new Doku_Parser_Mode_Formatting('superscript'));
        $this->P->parse("abc <sup>bar</sup> def");
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'abc ')),
            array('superscript_open',array()),
            array('cdata',array('bar')),
            array('superscript_close',array()),
            array('cdata',array(' def'."\n")),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEqual(array_map('stripbyteindex',$this->H->calls),$calls);
    }

    function testNotSuperscript() {
        $this->P->addMode('superscript',new Doku_Parser_Mode_Formatting('superscript'));
        $this->P->parse("abc <sup>bar def");
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\nabc <sup>bar def\n")),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEqual(array_map('stripbyteindex',$this->H->calls),$calls);
    }

    function testDeleted() {
        $this->P->addMode('deleted',new Doku_Parser_Mode_Formatting('deleted'));
        $this->P->parse('abc <del>bar</del> def');
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'abc ')),
            array('deleted_open',array()),
            array('cdata',array('bar')),
            array('deleted_close',array()),
            array('cdata',array(' def'."\n")),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEqual(array_map('stripbyteindex',$this->H->calls),$calls);
    }
    
    function testNotDeleted() {
        $this->P->addMode('deleted',new Doku_Parser_Mode_Formatting('deleted'));
        $this->P->parse('abc <del>bar def');
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\nabc <del>bar def\n")),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEqual(array_map('stripbyteindex',$this->H->calls),$calls);
    }

    function testNestedFormatting() {
        $this->P->addMode('strong',new Doku_Parser_Mode_Formatting('strong'));
        $this->P->addMode('emphasis',new Doku_Parser_Mode_Formatting('emphasis'));
        $this->P->parse('abc **a//b//c** def');
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'abc ')),
            array('strong_open',array()),
            array('cdata',array('a')),
            array('emphasis_open',array()),
            array('cdata',array('b')),
            array('emphasis_close',array()),
            array('cdata',array('c')),
            array('strong_close',array()),
            array('cdata',array(' def'."\n")),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEqual(array_map('stripbyteindex',$this->H->calls),$calls);
    }
    
    function testIllegalNestedFormatting() {
        $this->P->addMode('strong',new Doku_Parser_Mode_Formatting('strong'));
        $this->P->parse('abc **a**b**c** def');
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'abc ')),
            array('strong_open',array()),
            array('cdata',array('a')),
            array('strong_close',array()),
            array('cdata',array('b')),
            array('strong_open',array()),
            array('cdata',array('c')),
            array('strong_close',array()),
            array('cdata',array(' def'."\n")),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEqual(array_map('stripbyteindex',$this->H->calls),$calls);
    }
}

/**
* Conditional test runner
*/
if (!defined('TEST_RUNNING')) {
    define('TEST_RUNNING', true);
    $test = &new TestOfDoku_Parser_Formatting();
    $test->run(new HtmlReporter());
}
