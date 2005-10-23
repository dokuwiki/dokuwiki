<?php
require_once 'parser.test.php';

class TestOfDoku_Parser_TocSections extends TestOfDoku_Parser {

    function TestOfDoku_Parser_TocSections() {
        $this->UnitTestCase('TestOfDoku_Parser_TocSections');
    }
    
   
    function testNoToc() {
        $this->P->addMode('header',new Doku_Parser_Mode_Header());
        $this->P->addMode('notoc',new Doku_Parser_Mode_NoToc());
        $this->P->addMode('eol',new Doku_Parser_Mode_Eol());
        $this->P->parse('abc
~~NOTOC~~
def
====== HeaderX ======
X
====== HeaderY ======
Y
====== HeaderZ ======
Z
');
        $calls = array(
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("abc")),
            array('p_close',array()),
            array('p_open',array()),
            array('p_close',array()),
            array('p_open',array()),
            array('cdata',array("def")),
            array('p_close',array()),
            array('p_open',array()),
            array('p_close',array()),
            array('header',array(' HeaderX ',1)),
            array('section_open',array(1)),
            array('p_open',array()),
            array('cdata',array("X")),
            array('p_close',array()),
            array('p_open',array()),
            array('p_close',array()),
            array('section_close',array()),
            array('header',array(' HeaderY ',1)),
            array('section_open',array(1)),
            array('p_open',array()),
            array('cdata',array("Y")),
            array('p_close',array()),
            array('p_open',array()),
            array('p_close',array()),
            array('section_close',array()),
            array('header',array(' HeaderZ ',1)),
            array('section_open',array(1)),
            array('p_open',array()),
            array('cdata',array("Z")),
            array('p_close',array()),
            array('p_open',array()),
            array('p_close',array()),
            array('p_open',array()),
            array('p_close',array()),
            array('section_close',array()),
            array('document_end',array()),
        );
        $this->assertEqual(array_map('stripByteIndex',$this->H->calls),$calls);
    }
    
    function testTocSameLevel() {
        $this->P->addMode('header',new Doku_Parser_Mode_Header());
        $this->P->addMode('eol',new Doku_Parser_Mode_Eol());
        $this->P->parse('abc
def
====== HeaderX ======
X
====== HeaderY ======
Y
====== HeaderZ ======
Z
');
        $calls = array(
            array('document_start',array()),
            array('toc_open',array()),
            array('tocbranch_open',array(1)),
            array('tocitem_open',array(1)),
            array('tocelement',array(1,' HeaderX ')),
            array('tocitem_close',array(1)),
            array('tocitem_open',array(1)),
            array('tocelement',array(1,' HeaderY ')),
            array('tocitem_close',array(1)),
            array('tocitem_open',array(1)),
            array('tocelement',array(1,' HeaderZ ')),
            array('tocitem_close',array(1)),
            array('tocbranch_close',array(1)),
            array('toc_close',array()),
            array('p_open',array()),
            array('cdata',array("abc")),
            array('p_close',array()),
            array('p_open',array()),
            array('cdata',array("def")),
            array('p_close',array()),
            array('p_open',array()),
            array('p_close',array()),
            array('header',array(' HeaderX ',1)),
            array('section_open',array(1)),
            array('p_open',array()),
            array('cdata',array("X")),
            array('p_close',array()),
            array('p_open',array()),
            array('p_close',array()),
            array('section_close',array()),
            array('header',array(' HeaderY ',1)),
            array('section_open',array(1)),
            array('p_open',array()),
            array('cdata',array("Y")),
            array('p_close',array()),
            array('p_open',array()),
            array('p_close',array()),
            array('section_close',array()),
            array('header',array(' HeaderZ ',1)),
            array('section_open',array(1)),
            array('p_open',array()),
            array('cdata',array("Z")),
            array('p_close',array()),
            array('p_open',array()),
            array('p_close',array()),
            array('p_open',array()),
            array('p_close',array()),
            array('section_close',array()),
            array('document_end',array()),
        );
        $this->assertEqual(array_map('stripByteIndex',$this->H->calls),$calls);
    }
    
    function testTocDeepening() {
        $this->P->addMode('header',new Doku_Parser_Mode_Header());
        $this->P->addMode('eol',new Doku_Parser_Mode_Eol());
        $this->P->parse('abc
def
====== HeaderX ======
X
===== HeaderY =====
Y
==== HeaderZ ====
Z
');
        $calls = array(
            array('document_start',array()),
            array('toc_open',array()),
            array('tocbranch_open',array(1)),
            array('tocitem_open',array(1)),
            array('tocelement',array(1,' HeaderX ')),
            array('tocbranch_open',array(2)),
            array('tocitem_open',array(2)),
            array('tocelement',array(2,' HeaderY ')),
            array('tocbranch_open',array(3)),
            array('tocitem_open',array(3)),
            array('tocelement',array(3,' HeaderZ ')),
            array('tocitem_close',array(3)),
            array('tocbranch_close',array(3)),
            array('tocitem_close',array(2)),
            array('tocbranch_close',array(2)),
            array('tocitem_close',array(1)),
            array('tocbranch_close',array(1)),
            array('toc_close',array()),
            array('p_open',array()),
            array('cdata',array("abc")),
            array('p_close',array()),
            array('p_open',array()),
            array('cdata',array("def")),
            array('p_close',array()),
            array('p_open',array()),
            array('p_close',array()),
            array('header',array(' HeaderX ',1)),
            array('section_open',array(1)),
            array('p_open',array()),
            array('cdata',array("X")),
            array('p_close',array()),
            array('p_open',array()),
            array('p_close',array()),
            array('section_close',array()),
            array('header',array(' HeaderY ',2)),
            array('section_open',array(2)),
            array('p_open',array()),
            array('cdata',array("Y")),
            array('p_close',array()),
            array('p_open',array()),
            array('p_close',array()),
            array('section_close',array()),
            array('header',array(' HeaderZ ',3)),
            array('section_open',array(3)),
            array('p_open',array()),
            array('cdata',array("Z")),
            array('p_close',array()),
            array('p_open',array()),
            array('p_close',array()),
            array('p_open',array()),
            array('p_close',array()),
            array('section_close',array()),
            array('document_end',array()),
        );
        $this->assertEqual(array_map('stripByteIndex',$this->H->calls),$calls);
    }
    
    function testTocShallower() {
        $this->P->addMode('header',new Doku_Parser_Mode_Header());
        $this->P->addMode('eol',new Doku_Parser_Mode_Eol());
        $this->P->parse('abc
def
==== HeaderX ====
X
===== HeaderY =====
Y
====== HeaderZ ======
Z
');
        $calls = array(
            array('document_start',array()),
            array('toc_open',array()),
            array('tocbranch_open',array(1)),
            array('tocitem_open',array(1,TRUE)),
            array('tocbranch_open',array(2)),
            array('tocitem_open',array(2,TRUE)),
            array('tocbranch_open',array(3)),
            array('tocitem_open',array(3)),
            array('tocelement',array(3,' HeaderX ')),
            array('tocitem_close',array(3)),
            array('tocbranch_close',array(3)),
            array('tocitem_close',array(2)),
            array('tocitem_open',array(2)),
            array('tocelement',array(2,' HeaderY ')),
            array('tocitem_close',array(2)),
            array('tocbranch_close',array(2)),
            array('tocitem_close',array(1)),
            array('tocitem_open',array(1)),
            array('tocelement',array(1,' HeaderZ ')),
            array('tocitem_close',array(1)),
            array('tocbranch_close',array(1)),
            array('toc_close',array()),
            array('p_open',array()),
            array('cdata',array("abc")),
            array('p_close',array()),
            array('p_open',array()),
            array('cdata',array("def")),
            array('p_close',array()),
            array('p_open',array()),
            array('p_close',array()),
            array('header',array(' HeaderX ',3)),
            array('section_open',array(3)),
            array('p_open',array()),
            array('cdata',array("X")),
            array('p_close',array()),
            array('p_open',array()),
            array('p_close',array()),
            array('section_close',array()),
            array('header',array(' HeaderY ',2)),
            array('section_open',array(2)),
            array('p_open',array()),
            array('cdata',array("Y")),
            array('p_close',array()),
            array('p_open',array()),
            array('p_close',array()),
            array('section_close',array()),
            array('header',array(' HeaderZ ',1)),
            array('section_open',array(1)),
            array('p_open',array()),
            array('cdata',array("Z")),
            array('p_close',array()),
            array('p_open',array()),
            array('p_close',array()),
            array('p_open',array()),
            array('p_close',array()),
            array('section_close',array()),
            array('document_end',array()),
        );
        $this->assertEqual(array_map('stripByteIndex',$this->H->calls),$calls);
    }

    function testTocNesting() {
        $this->P->addMode('header',new Doku_Parser_Mode_Header());
        $this->P->addMode('eol',new Doku_Parser_Mode_Eol());
        $this->P->parse('abc
def
====== HeaderX ======
X
===== HeaderY =====
Y
====== HeaderZ ======
Z
');
        $calls = array(
            array('document_start',array()),
            array('toc_open',array()),
            array('tocbranch_open',array(1)),
            array('tocitem_open',array(1)),
            array('tocelement',array(1,' HeaderX ')),
            array('tocbranch_open',array(2)),
            array('tocitem_open',array(2)),
            array('tocelement',array(2,' HeaderY ')),
            array('tocitem_close',array(2)),
            array('tocbranch_close',array(2)),
            array('tocitem_close',array(1)),
            array('tocitem_open',array(1)),
            array('tocelement',array(1,' HeaderZ ')),
            array('tocitem_close',array(1)),
            array('tocbranch_close',array(1)),
            array('toc_close',array()),
            array('p_open',array()),
            array('cdata',array("abc")),
            array('p_close',array()),
            array('p_open',array()),
            array('cdata',array("def")),
            array('p_close',array()),
            array('p_open',array()),
            array('p_close',array()),
            array('header',array(' HeaderX ',1)),
            array('section_open',array(1)),
            array('p_open',array()),
            array('cdata',array("X")),
            array('p_close',array()),
            array('p_open',array()),
            array('p_close',array()),
            array('section_close',array()),
            array('header',array(' HeaderY ',2)),
            array('section_open',array(2)),
            array('p_open',array()),
            array('cdata',array("Y")),
            array('p_close',array()),
            array('p_open',array()),
            array('p_close',array()),
            array('section_close',array()),
            array('header',array(' HeaderZ ',1)),
            array('section_open',array(1)),
            array('p_open',array()),
            array('cdata',array("Z")),
            array('p_close',array()),
            array('p_open',array()),
            array('p_close',array()),
            array('p_open',array()),
            array('p_close',array()),
            array('section_close',array()),
            array('document_end',array()),
        );
        $this->assertEqual(array_map('stripByteIndex',$this->H->calls),$calls);
    }
    
    function testTocNestingInverted() {
        $this->P->addMode('header',new Doku_Parser_Mode_Header());
        $this->P->addMode('eol',new Doku_Parser_Mode_Eol());
        $this->P->parse('abc
def
===== HeaderX =====
X
====== HeaderY ======
Y
===== HeaderZ =====
Z
');
        $calls = array(
            array('document_start',array()),
            array('toc_open',array()),
            array('tocbranch_open',array(1)),
            array('tocitem_open',array(1,TRUE)),
            array('tocbranch_open',array(2)),
            array('tocitem_open',array(2)),
            array('tocelement',array(2,' HeaderX ')),
            array('tocitem_close',array(2)),
            array('tocbranch_close',array(2)),
            array('tocitem_close',array(1)),
            array('tocitem_open',array(1)),
            array('tocelement',array(1,' HeaderY ')),
            array('tocbranch_open',array(2)),
            array('tocitem_open',array(2)),
            array('tocelement',array(2,' HeaderZ ')),
            array('tocitem_close',array(2)),
            array('tocbranch_close',array(2)),
            array('tocitem_close',array(1)),
            array('tocbranch_close',array(1)),
            array('toc_close',array()),
            array('p_open',array()),
            array('cdata',array("abc")),
            array('p_close',array()),
            array('p_open',array()),
            array('cdata',array("def")),
            array('p_close',array()),
            array('p_open',array()),
            array('p_close',array()),
            array('header',array(' HeaderX ',2)),
            array('section_open',array(2)),
            array('p_open',array()),
            array('cdata',array("X")),
            array('p_close',array()),
            array('p_open',array()),
            array('p_close',array()),
            array('section_close',array()),
            array('header',array(' HeaderY ',1)),
            array('section_open',array(1)),
            array('p_open',array()),
            array('cdata',array("Y")),
            array('p_close',array()),
            array('p_open',array()),
            array('p_close',array()),
            array('section_close',array()),
            array('header',array(' HeaderZ ',2)),
            array('section_open',array(2)),
            array('p_open',array()),
            array('cdata',array("Z")),
            array('p_close',array()),
            array('p_open',array()),
            array('p_close',array()),
            array('p_open',array()),
            array('p_close',array()),
            array('section_close',array()),
            array('document_end',array()),
        );
        $this->assertEqual(array_map('stripByteIndex',$this->H->calls),$calls);
    }
    
    function testTocAllLevels() {
        $this->P->addMode('header',new Doku_Parser_Mode_Header());
        $this->P->addMode('eol',new Doku_Parser_Mode_Eol());
        $this->P->parse('abc
def
====== HeaderV ======
V
===== HeaderW =====
W
==== HeaderX ====
X
=== HeaderY ===
Y
== HeaderZ ==
Z
');
        $calls = array(
            array('document_start',array()),
            array('toc_open',array()),
            array('tocbranch_open',array(1)),
            array('tocitem_open',array(1)),
            array('tocelement',array(1,' HeaderV ')),
            array('tocbranch_open',array(2)),
            array('tocitem_open',array(2)),
            array('tocelement',array(2,' HeaderW ')),
            array('tocbranch_open',array(3)),
            array('tocitem_open',array(3)),
            array('tocelement',array(3,' HeaderX ')),
            array('tocitem_close',array(3)),
            array('tocbranch_close',array(3)),
            array('tocitem_close',array(2)),
            array('tocbranch_close',array(2)),
            array('tocitem_close',array(1)),
            array('tocbranch_close',array(1)),
            array('toc_close',array()),
            array('p_open',array()),
            array('cdata',array("abc")),
            array('p_close',array()),
            array('p_open',array()),
            array('cdata',array("def")),
            array('p_close',array()),
            array('p_open',array()),
            array('p_close',array()),
            array('header',array(' HeaderV ',1)),
            array('section_open',array(1)),
            array('p_open',array()),
            array('cdata',array("V")),
            array('p_close',array()),
            array('p_open',array()),
            array('p_close',array()),
            array('section_close',array()),
            array('header',array(' HeaderW ',2)),
            array('section_open',array(2)),
            array('p_open',array()),
            array('cdata',array("W")),
            array('p_close',array()),
            array('p_open',array()),
            array('p_close',array()),
            array('section_close',array()),
            array('header',array(' HeaderX ',3)),
            array('section_open',array(3)),
            array('p_open',array()),
            array('cdata',array("X")),
            array('p_close',array()),
            array('p_open',array()),
            array('p_close',array()),
            array('section_close',array()),
            array('header',array(' HeaderY ',4)),
            array('section_open',array(4)),
            array('p_open',array()),
            array('cdata',array("Y")),
            array('p_close',array()),
            array('p_open',array()),
            array('p_close',array()),
            array('section_close',array()),
            array('header',array(' HeaderZ ',5)),
            array('section_open',array(5)),
            array('p_open',array()),
            array('cdata',array("Z")),
            array('p_close',array()),
            array('p_open',array()),
            array('p_close',array()),
            array('p_open',array()),
            array('p_close',array()),
            array('section_close',array()),
            array('document_end',array()),
        );
        $this->assertEqual(array_map('stripByteIndex',$this->H->calls),$calls);
    }
}

