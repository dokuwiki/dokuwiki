<?php
require_once 'parser.inc.php';

class TestOfDoku_Parser_Headers extends TestOfDoku_Parser {

    function testHeader1() {
        $this->P->addMode('header',new Doku_Parser_Mode_Header());
        $this->P->parse("abc \n ====== Header ====== \n def");
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\nabc ")),
            array('p_close',array()),
            array('header',array('Header',1,6)),
            array('section_open',array(1)),
            array('p_open',array()),
            array('cdata',array("\n def")),
            array('p_close',array()),
            array('section_close',array()),
            array('document_end',array()),
        );
        $this->assertEquals(array_map('stripByteIndex',$this->H->calls),$calls);
    }

    function testHeader2() {
        $this->P->addMode('header',new Doku_Parser_Mode_Header());
        $this->P->parse("abc \n  ===== Header ===== \n def");
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\nabc ")),
            array('p_close',array()),
            array('header',array('Header',2,6)),
            array('section_open',array(2)),
            array('p_open',array()),
            array('cdata',array("\n def")),
            array('p_close',array()),
            array('section_close',array()),
            array('document_end',array()),
        );
        $this->assertEquals(array_map('stripByteIndex',$this->H->calls),$calls);
    }

    function testHeader3() {
        $this->P->addMode('header',new Doku_Parser_Mode_Header());
        $this->P->parse("abc \n ==== Header ==== \n def");
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\nabc ")),
            array('p_close',array()),
            array('header',array('Header',3,6)),
            array('section_open',array(3)),
            array('p_open',array()),
            array('cdata',array("\n def")),
            array('p_close',array()),
            array('section_close',array()),
            array('document_end',array()),
        );
        $this->assertEquals(array_map('stripByteIndex',$this->H->calls),$calls);
    }

    function testHeader4() {
        $this->P->addMode('header',new Doku_Parser_Mode_Header());
        $this->P->parse("abc \n === Header === \n def");
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\nabc ")),
            array('p_close',array()),
            array('header',array('Header',4,6)),
            array('section_open',array(4)),
            array('p_open',array()),
            array('cdata',array("\n def")),
            array('p_close',array()),
            array('section_close',array()),
            array('document_end',array()),
        );
        $this->assertEquals(array_map('stripByteIndex',$this->H->calls),$calls);
    }

    function testHeader5() {
        $this->P->addMode('header',new Doku_Parser_Mode_Header());
        $this->P->parse("abc \n  == Header ==  \n def");
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\nabc ")),
            array('p_close',array()),
            array('header',array('Header',5,6)),
            array('section_open',array(5)),
            array('p_open',array()),
            array('cdata',array("\n def")),
            array('p_close',array()),
            array('section_close',array()),
            array('document_end',array()),
        );
        $this->assertEquals(array_map('stripByteIndex',$this->H->calls),$calls);
    }

    function testHeader2UnevenSmaller() {
        $this->P->addMode('header',new Doku_Parser_Mode_Header());
        $this->P->parse("abc \n  ===== Header ==  \n def");
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\nabc ")),
            array('p_close',array()),
            array('header',array('Header',2,6)),
            array('section_open',array(2)),
            array('p_open',array()),
            array('cdata',array("\n def")),
            array('p_close',array()),
            array('section_close',array()),
            array('document_end',array()),
        );
        $this->assertEquals(array_map('stripByteIndex',$this->H->calls),$calls);
    }

    function testHeader2UnevenBigger() {
        $this->P->addMode('header',new Doku_Parser_Mode_Header());
        $this->P->parse("abc \n  ===== Header ===========  \n def");
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\nabc ")),
            array('p_close',array()),
            array('header',array('Header',2,6)),
            array('section_open',array(2)),
            array('p_open',array()),
            array('cdata',array("\n def")),
            array('p_close',array()),
            array('section_close',array()),
            array('document_end',array()),
        );
        $this->assertEquals(array_map('stripByteIndex',$this->H->calls),$calls);
    }

    function testHeaderLarge() {
        $this->P->addMode('header',new Doku_Parser_Mode_Header());
        $this->P->parse("abc \n ======= Header ======= \n def");
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\nabc ")),
            array('p_close',array()),
            array('header',array('Header',1,6)),
            array('section_open',array(1)),
            array('p_open',array()),
            array('cdata',array("\n def")),
            array('p_close',array()),
            array('section_close',array()),
            array('document_end',array()),
        );
        $this->assertEquals(array_map('stripByteIndex',$this->H->calls),$calls);
    }

    function testHeaderSmall() {
        $this->P->addMode('header',new Doku_Parser_Mode_Header());
        $this->P->parse("abc \n= Header =\n def");
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\nabc \n= Header =\n def")),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEquals(array_map('stripByteIndex',$this->H->calls),$calls);
    }


    function testHeader1Mixed() {
        $this->P->addMode('header',new Doku_Parser_Mode_Header());
        $this->P->parse("abc \n====== == Header == ======\n def");
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\nabc ")),
            array('p_close',array()),
            array('header',array('== Header ==',1,6)),
            array('section_open',array(1)),
            array('p_open',array()),
            array('cdata',array("\n def")),
            array('p_close',array()),
            array('section_close',array()),
            array('document_end',array()),
        );
        $this->assertEquals(array_map('stripByteIndex',$this->H->calls),$calls);
    }

    function testHeader5Mixed() {
        $this->P->addMode('header',new Doku_Parser_Mode_Header());
        $this->P->parse("abc \n== ====== Header ====== ==\n def");
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\nabc ")),
            array('p_close',array()),
            array('header',array('====== Header ======',5,6)),
            array('section_open',array(5)),
            array('p_open',array()),
            array('cdata',array("\n def")),
            array('p_close',array()),
            array('section_close',array()),
            array('document_end',array()),
        );
        $this->assertEquals(array_map('stripByteIndex',$this->H->calls),$calls);
    }

    function testHeaderMultiline() {
        $this->P->addMode('header',new Doku_Parser_Mode_Header());
        $this->P->parse("abc \n== ====== Header\n ====== ==\n def");
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\nabc \n== ====== Header")),
            array('p_close',array()),
            array('header',array('',1,23)),
            array('section_open',array(1)),
            array('p_open',array()),
            array('cdata',array("\n def")),
            array('p_close',array()),
            array('section_close',array()),
            array('document_end',array()),
        );
        $this->assertEquals(array_map('stripByteIndex',$this->H->calls),$calls);
    }

#    function testNoToc() {
#        $this->P->addMode('notoc',new Doku_Parser_Mode_NoToc());
#        $this->P->parse('abc ~~NOTOC~~ def');
#        $this->assertFalse($this->H->meta['toc']);
#    }

    function testHeader1Eol() {
        $this->P->addMode('header',new Doku_Parser_Mode_Header());
        $this->P->addMode('eol',new Doku_Parser_Mode_Eol());
        $this->P->parse("abc \n ====== Header ====== \n def");
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array('abc ')),
            array('p_close',array()),
            array('header',array('Header',1, 6)),
            array('section_open',array(1)),
            array('p_open',array()),
            array('cdata',array(' def')),
            array('p_close',array()),
            array('section_close',array()),
            array('document_end',array()),
        );
        $this->assertEquals(array_map('stripByteIndex',$this->H->calls),$calls);

    }

    function testHeaderMulti2() {
        $this->P->addMode('header',new Doku_Parser_Mode_Header());
        $this->P->parse("abc \n ====== Header ====== \n def abc \n ===== Header2 ===== \n def");
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\nabc ")),
            array('p_close',array()),
            array('header',array('Header',1,6)),
            array('section_open',array(1)),
            array('p_open',array()),
            array('cdata',array("\n def abc ")),
            array('p_close',array()),
            array('section_close',array()),
            array('header',array('Header2',2,39)),
            array('section_open',array(2)),
            array('p_open',array()),
            array('cdata',array("\n def")),
            array('p_close',array()),
            array('section_close',array()),
            array('document_end',array())
        );
        $this->assertEquals(array_map('stripByteIndex',$this->H->calls),$calls);
    }

}

