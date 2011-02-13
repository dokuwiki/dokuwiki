<?php
require_once 'parser.inc.php';

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
            array('cdata',array(' def')),
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
            array('cdata',array("\nabc **bar def")),
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
            array('cdata',array(' def')),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEqual(array_map('stripbyteindex',$this->H->calls),$calls);
    }

    function testEmColon() {
        $this->P->addMode('emphasis',new Doku_Parser_Mode_Formatting('emphasis'));
        $this->P->parse('abc //Тест: // def');
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'abc ')),
            array('emphasis_open',array()),
            array('cdata',array('Тест: ')),
            array('emphasis_close',array()),
            array('cdata',array(' def')),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEqual(array_map('stripbyteindex',$this->H->calls),$calls);
    }

    function testEmSingleChar() {
        $this->P->addMode('emphasis',new Doku_Parser_Mode_Formatting('emphasis'));
        $this->P->parse('abc //b// def');
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'abc ')),
            array('emphasis_open',array()),
            array('cdata',array('b')),
            array('emphasis_close',array()),
            array('cdata',array(' def')),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEqual(array_map('stripbyteindex',$this->H->calls),$calls);
    }

    function testEmWithUnknownSchema() {
        $this->P->addMode('emphasis',new Doku_Parser_Mode_Formatting('emphasis'));
        $this->P->parse('abc //foo:// bar// def');
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'abc ')),
            array('emphasis_open',array()),
            array('cdata',array('foo:')),
            array('emphasis_close',array()),
            array('cdata',array(' bar// def')),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEqual(array_map('stripbyteindex',$this->H->calls),$calls);
    }

    function testEmWithKnownSchema() {
        $this->P->addMode('emphasis',new Doku_Parser_Mode_Formatting('emphasis'));
        $this->P->addMode('externallink',new Doku_Parser_Mode_ExternalLink());
        $this->P->parse('abc //foo http://www.google.com bar// def');
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'abc ')),
            array('emphasis_open',array()),
            array('cdata',array('foo ')),
            array('externallink',array('http://www.google.com', NULL)),
            array('cdata',array(' bar')),
            array('emphasis_close',array()),
            array('cdata',array(' def')),
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
            array('cdata',array("\nabc //bar def")),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEqual(array_map('stripbyteindex',$this->H->calls),$calls);
    }

    function testNotEmSchemaAtOpen() {
        $this->P->addMode('emphasis',new Doku_Parser_Mode_Formatting('emphasis'));
        $this->P->parse('abc foo://bar// def');
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'abc foo:')),
            array('emphasis_open',array()),
            array('cdata',array('bar')),
            array('emphasis_close',array()),
            array('cdata',array(' def')),
                        array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEqual(array_map('stripbyteindex',$this->H->calls),$calls);
    }

    function testNotEmSchemaAtClose() {
        $this->P->addMode('emphasis',new Doku_Parser_Mode_Formatting('emphasis'));
        $this->P->parse('abc //http:// def');
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\nabc //http:// def")),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEqual(array_map('stripbyteindex',$this->H->calls),$calls);
    }

    function testEmWithMultiOccurence() {
        // Case from #763
        $this->P->addMode('emphasis',new Doku_Parser_Mode_Formatting('emphasis'));
        $this->P->parse('//text:// Blablabla Blablabla

//text:// another Blablabla Blablabla');
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n")),
            array('emphasis_open',array()),
            array('cdata',array('text:')),
            array('emphasis_close',array()),
            array('cdata',array(" Blablabla Blablabla\n\n")),
            array('emphasis_open',array()),
            array('cdata',array('text:')),
            array('emphasis_close',array()),
            array('cdata',array(" another Blablabla Blablabla")),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEqual(array_map('stripbyteindex',$this->H->calls),$calls);
    }

    function testEmWithUnicode() {
        // Case from #1468
        $this->P->addMode('emphasis',new Doku_Parser_Mode_Formatting('emphasis'));
        $this->P->parse('//Тест://');
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n")),
            array('emphasis_open',array()),
            array('cdata',array('Тест:')),
            array('emphasis_close',array()),
            array('cdata', array('')),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEqual(array_map('stripbyteindex',$this->H->calls),$calls);
    }

    function testNoEmWithInvalidURL() {
        // Case from #1629
        $this->P->addMode('emphasis',new Doku_Parser_Mode_Formatting('emphasis'));
        $this->P->parse('http://<CertificateServerName>/certsrv/certcarc.asp');
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array('http://<CertificateServerName>/certsrv/certcarc.asp')),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEqual(array_map('stripbyteindex',$this->H->calls),$calls);
    }

    function testNoEmWithUnknownURL() {
        // Case from #1640
        $this->P->addMode('emphasis',new Doku_Parser_Mode_Formatting('emphasis'));
        $this->P->parse('svn://example.com/foo/bar');
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array('svn://example.com/foo/bar')),
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
            array('cdata',array(' def')),
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
            array('cdata',array("\nabc __bar def")),
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
            array('cdata',array(' def')),
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
            array('cdata',array("\nabc ''bar def")),
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
            array('cdata',array(' def')),
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
            array('cdata',array("\nabc <sub>bar def")),
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
            array('cdata',array(' def')),
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
            array('cdata',array("\nabc <sup>bar def")),
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
            array('cdata',array(' def')),
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
            array('cdata',array("\nabc <del>bar def")),
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
            array('cdata',array(' def')),
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
            array('cdata',array(' def')),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEqual(array_map('stripbyteindex',$this->H->calls),$calls);
    }
}

