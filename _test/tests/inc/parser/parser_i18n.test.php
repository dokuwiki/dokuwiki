<?php
require_once 'parser.inc.php';

class TestOfDoku_Parser_i18n extends TestOfDoku_Parser {

    function testFormatting() {
        $formats = array (
            'strong', 'emphasis', 'underline', 'monospace',
            'subscript', 'superscript', 'deleted',
        );
        foreach ( $formats as $format ) {
            $this->P->addMode($format,new Doku_Parser_Mode_Formatting($format));
        }
        $this->P->parse("I**ñ**t__ë__r//n//â<sup>t</sup>i<sub>ô</sub>n''à''liz<del>æ</del>tiøn");
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\nI")),
            array('strong_open',array()),
            array('cdata',array('ñ')),
            array('strong_close',array()),
            array('cdata',array('t')),
            array('underline_open',array()),
            array('cdata',array('ë')),
            array('underline_close',array()),
            array('cdata',array('r')),
            array('emphasis_open',array()),
            array('cdata',array('n')),
            array('emphasis_close',array()),
            array('cdata',array('â')),
            array('superscript_open',array()),
            array('cdata',array('t')),
            array('superscript_close',array()),
            array('cdata',array('i')),
            array('subscript_open',array()),
            array('cdata',array('ô')),
            array('subscript_close',array()),
            array('cdata',array('n')),
            array('monospace_open',array()),
            array('cdata',array('à')),
            array('monospace_close',array()),
            array('cdata',array('liz')),
            array('deleted_open',array()),
            array('cdata',array('æ')),
            array('deleted_close',array()),
            array('cdata',array("tiøn")),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEquals(array_map('stripbyteindex',$this->H->calls),$calls);
    }

    function testHeader() {
        $this->P->addMode('header',new Doku_Parser_Mode_Header());
        $this->P->parse("Foo\n ==== Iñtërnâtiônàlizætiøn ==== \n Bar");
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\nFoo")),
            array('p_close',array()),
            array('header',array('Iñtërnâtiônàlizætiøn',3,5)),
            array('section_open',array(3)),
            array('p_open',array()),
            array('cdata',array("\n Bar")),
            array('p_close',array()),
            array('section_close',array()),
            array('document_end',array()),
        );
        $this->assertEquals(array_map('stripbyteindex',$this->H->calls),$calls);
    }

    function testTable() {
        $this->P->addMode('table',new Doku_Parser_Mode_Table());
        $this->P->parse('
abc
| Row 0 Col 1    | Iñtërnâtiônàlizætiøn     | Row 0 Col 3        |
| Row 1 Col 1    | Iñtërnâtiônàlizætiøn     | Row 1 Col 3        |
def');
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n\nabc")),
            array('p_close',array()),
            array('table_open',array(3, 2, 6)),
            array('tablerow_open',array()),
            array('tablecell_open',array(1,'left',1)),
            array('cdata',array(' Row 0 Col 1    ')),
            array('tablecell_close',array()),
            array('tablecell_open',array(1,'left',1)),
            array('cdata',array(' Iñtërnâtiônàlizætiøn     ')),
            array('tablecell_close',array()),
            array('tablecell_open',array(1,'left',1)),
            array('cdata',array(' Row 0 Col 3        ')),
            array('tablecell_close',array()),
            array('tablerow_close',array()),
            array('tablerow_open',array()),
            array('tablecell_open',array(1,'left',1)),
            array('cdata',array(' Row 1 Col 1    ')),
            array('tablecell_close',array()),
            array('tablecell_open',array(1,'left',1)),
            array('cdata',array(' Iñtërnâtiônàlizætiøn     ')),
            array('tablecell_close',array()),
            array('tablecell_open',array(1,'left',1)),
            array('cdata',array(' Row 1 Col 3        ')),
            array('tablecell_close',array()),
            array('tablerow_close',array()),
            array('table_close',array(153)),
            array('p_open',array()),
            array('cdata',array('def')),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEquals(array_map('stripbyteindex',$this->H->calls),$calls);
    }

    function testAcronym() {
        $t = array('Iñtërnâtiônàlizætiøn');
        $this->P->addMode('acronym',new Doku_Parser_Mode_Acronym($t));
        $this->P->parse("Foo Iñtërnâtiônàlizætiøn Bar");
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\nFoo ")),
            array('acronym',array('Iñtërnâtiônàlizætiøn')),
            array('cdata',array(" Bar")),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEquals(array_map('stripbyteindex',$this->H->calls),$calls);
    }

    function testInterwiki() {
        $this->P->addMode('internallink',new Doku_Parser_Mode_InternalLink());
        $this->P->parse("Foo [[wp>Iñtërnâtiônàlizætiøn|Iñtërnâtiônàlizætiøn]] Bar");
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'Foo ')),
            array('interwikilink',array('wp>Iñtërnâtiônàlizætiøn','Iñtërnâtiônàlizætiøn','wp','Iñtërnâtiônàlizætiøn')),
            array('cdata',array(' Bar')),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEquals(array_map('stripByteIndex',$this->H->calls),$calls);
    }

    function testInternalLink() {
        $this->P->addMode('internallink',new Doku_Parser_Mode_InternalLink());
        $this->P->parse("Foo [[x:Iñtërnâtiônàlizætiøn:y:foo_bar:z|Iñtërnâtiônàlizætiøn]] Bar");
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'Foo ')),
            array('internallink',array('x:Iñtërnâtiônàlizætiøn:y:foo_bar:z','Iñtërnâtiônàlizætiøn')),
            array('cdata',array(' Bar')),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEquals(array_map('stripByteIndex',$this->H->calls),$calls);
    }
}

