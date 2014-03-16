<?php
require_once 'parser.inc.php';

class TestOfDoku_Parser_Table extends TestOfDoku_Parser {

    function testTable() {
        $this->P->addMode('table',new Doku_Parser_Mode_Table());
        $this->P->parse('
abc
| Row 0 Col 1    | Row 0 Col 2     | Row 0 Col 3        |
| Row 1 Col 1    | Row 1 Col 2     | Row 1 Col 3        |
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
            array('cdata',array(' Row 0 Col 2     ')),
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
            array('cdata',array(' Row 1 Col 2     ')),
            array('tablecell_close',array()),
            array('tablecell_open',array(1,'left',1)),
            array('cdata',array(' Row 1 Col 3        ')),
            array('tablecell_close',array()),
            array('tablerow_close',array()),
            array('table_close',array(121)),
            array('p_open',array()),
            array('cdata',array('def')),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEquals($calls,array_map('stripbyteindex',$this->H->calls));
    }

    function testTableWinEOL() {
        $this->P->addMode('table',new Doku_Parser_Mode_Table());
        $this->P->parse("\r\nabc\r\n| Row 0 Col 1    | Row 0 Col 2     | Row 0 Col 3        |\r\n| Row 1 Col 1    | Row 1 Col 2     | Row 1 Col 3        |\r\ndef");
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
            array('cdata',array(' Row 0 Col 2     ')),
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
            array('cdata',array(' Row 1 Col 2     ')),
            array('tablecell_close',array()),
            array('tablecell_open',array(1,'left',1)),
            array('cdata',array(' Row 1 Col 3        ')),
            array('tablecell_close',array()),
            array('tablerow_close',array()),
            array('table_close',array(121)),
            array('p_open',array()),
            array('cdata',array('def')),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEquals($calls,array_map('stripbyteindex',$this->H->calls));
    }

    function testEmptyTable() {
        $this->P->addMode('table',new Doku_Parser_Mode_Table());
        $this->P->parse('
abc
|
def');
        
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n\nabc")),
            array('p_close',array()),
            array('table_open',array(0, 1, 6)),
            array('tablerow_open',array()),
            array('tablerow_close',array()),
            array('table_close',array(7)),
            array('p_open',array()),
            array('cdata',array('def')),
            array('p_close',array()),
            array('document_end',array()),
        );

        $this->assertEquals($calls,array_map('stripbyteindex',$this->H->calls));
    }
    
    function testTableHeaders() {
        $this->P->addMode('table',new Doku_Parser_Mode_Table());
        $this->P->parse('
abc
^ X | Y ^ Z |
def');
    
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n\nabc")),
            array('p_close',array()),
            array('table_open',array(3, 1, 6)),
            array('tablerow_open',array()),
            array('tableheader_open',array(1,NULL,1)),
            array('cdata',array(' X ')),
            array('tableheader_close',array()),
            array('tablecell_open',array(1,NULL,1)),
            array('cdata',array(' Y ')),
            array('tablecell_close',array()),
            array('tableheader_open',array(1,NULL,1)),
            array('cdata',array(' Z ')),
            array('tableheader_close',array()),
            array('tablerow_close',array()),
            array('table_close',array(19)),
            array('p_open',array()),
            array('cdata',array('def')),
            array('p_close',array()),
            array('document_end',array()),
        );

        $this->assertEquals($calls,array_map('stripbyteindex',$this->H->calls));

    }

    function testTableHead() {
        $this->P->addMode('table',new Doku_Parser_Mode_Table());
        $this->P->parse('
abc
^ X ^ Y ^ Z ^
| x | y | z |
def');

        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n\nabc")),
            array('p_close',array()),
            array('table_open',array(3, 2, 6)),
            array('tablethead_open',array()),
            array('tablerow_open',array()),
            array('tableheader_open',array(1,NULL,1)),
            array('cdata',array(' X ')),
            array('tableheader_close',array()),
            array('tableheader_open',array(1,NULL,1)),
            array('cdata',array(' Y ')),
            array('tableheader_close',array()),
            array('tableheader_open',array(1,NULL,1)),
            array('cdata',array(' Z ')),
            array('tableheader_close',array()),
            array('tablerow_close',array()),
            array('tablethead_close',array()),
            array('tablerow_open',array()),
            array('tablecell_open',array(1,NULL,1)),
            array('cdata',array(' x ')),
            array('tablecell_close',array()),
            array('tablecell_open',array(1,NULL,1)),
            array('cdata',array(' y ')),
            array('tablecell_close',array()),
            array('tablecell_open',array(1,NULL,1)),
            array('cdata',array(' z ')),
            array('tablecell_close',array()),
            array('tablerow_close',array()),
            array('table_close',array(33)),
            array('p_open',array()),
            array('cdata',array('def')),
            array('p_close',array()),
            array('document_end',array()),
        );

        $this->assertEquals($calls,array_map('stripbyteindex',$this->H->calls));

    }

    function testTableHeadOneRowTable() {
        $this->P->addMode('table',new Doku_Parser_Mode_Table());
        $this->P->parse('
abc
^ X ^ Y ^ Z ^
def');

        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n\nabc")),
            array('p_close',array()),
            array('table_open',array(3, 1, 6)),
            array('tablerow_open',array()),
            array('tableheader_open',array(1,NULL,1)),
            array('cdata',array(' X ')),
            array('tableheader_close',array()),
            array('tableheader_open',array(1,NULL,1)),
            array('cdata',array(' Y ')),
            array('tableheader_close',array()),
            array('tableheader_open',array(1,NULL,1)),
            array('cdata',array(' Z ')),
            array('tableheader_close',array()),
            array('tablerow_close',array()),
            array('table_close',array(19)),
            array('p_open',array()),
            array('cdata',array('def')),
            array('p_close',array()),
            array('document_end',array()),
        );

        $this->assertEquals($calls,array_map('stripbyteindex',$this->H->calls));

    }

    function testTableHeadMultiline() {
        $this->P->addMode('table',new Doku_Parser_Mode_Table());
        $this->P->parse('
abc
^ X1 ^ Y1 ^ Z1 ^
^ X2 ^ Y2 ^ Z2 ^
| A | B | C |
def');

        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n\nabc")),
            array('p_close',array()),
            array('table_open',array(3, 3, 6)),
            array('tablethead_open',array()),
            array('tablerow_open',array()),
            array('tableheader_open',array(1,NULL,1)),
            array('cdata',array(' X1 ')),
            array('tableheader_close',array()),
            array('tableheader_open',array(1,NULL,1)),
            array('cdata',array(' Y1 ')),
            array('tableheader_close',array()),
            array('tableheader_open',array(1,NULL,1)),
            array('cdata',array(' Z1 ')),
            array('tableheader_close',array()),
            array('tablerow_close',array()),
            array('tablerow_open',array()),
            array('tableheader_open',array(1,NULL,1)),
            array('cdata',array(' X2 ')),
            array('tableheader_close',array()),
            array('tableheader_open',array(1,NULL,1)),
            array('cdata',array(' Y2 ')),
            array('tableheader_close',array()),
            array('tableheader_open',array(1,NULL,1)),
            array('cdata',array(' Z2 ')),
            array('tableheader_close',array()),
            array('tablerow_close',array()),
            array('tablethead_close',array()),
            array('tablerow_open',array()),
            array('tablecell_open',array(1,NULL,1)),
            array('cdata',array(' A ')),
            array('tablecell_close',array()),
            array('tablecell_open',array(1,NULL,1)),
            array('cdata',array(' B ')),
            array('tablecell_close',array()),
            array('tablecell_open',array(1,NULL,1)),
            array('cdata',array(' C ')),
            array('tablecell_close',array()),
            array('tablerow_close',array()),
            array('table_close',array(53)),
            array('p_open',array()),
            array('cdata',array('def')),
            array('p_close',array()),
            array('document_end',array()),
        );

        $this->assertEquals($calls,array_map('stripbyteindex',$this->H->calls));

    }
    
    function testCellAlignment() {
        $this->P->addMode('table',new Doku_Parser_Mode_Table());
        $this->P->parse('
abc
|  X | Y  ^  Z  |
def');
    
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n\nabc")),
            array('p_close',array()),
            array('table_open',array(3, 1, 6)),
            array('tablerow_open',array()),
            array('tablecell_open',array(1,'right',1)),
            array('cdata',array('  X ')),
            array('tablecell_close',array()),
            array('tablecell_open',array(1,'left',1)),
            array('cdata',array(' Y  ')),
            array('tablecell_close',array()),
            array('tableheader_open',array(1,'center',1)),
            array('cdata',array('  Z  ')),
            array('tableheader_close',array()),
            array('tablerow_close',array()),
            array('table_close',array(23)),
            array('p_open',array()),
            array('cdata',array('def')),
            array('p_close',array()),
            array('document_end',array()),
        );

        $this->assertEquals($calls,array_map('stripbyteindex',$this->H->calls));
    }
    
    function testCellSpan() {
        $this->P->addMode('table',new Doku_Parser_Mode_Table());
        $this->P->parse('
abc
|  d || e |
| f ^ ^|
||||
def');
        
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n\nabc")),
            array('p_close',array()),
            array('table_open',array(3, 3, 6)),
            array('tablerow_open',array()),
            array('tablecell_open',array(2,'right',1)),
            array('cdata',array('  d ')),
            array('tablecell_close',array()),
            array('tablecell_open',array(1,NULL,1)),
            array('cdata',array(' e ')),
            array('tablecell_close',array()),
            array('tablerow_close',array()),
            array('tablerow_open',array()),
            array('tablecell_open',array(1,NULL,1)),
            array('cdata',array(' f ')),
            array('tablecell_close',array()),
            array('tableheader_open',array(2,NULL,1)),
            array('cdata',array(' ')),
            array('tableheader_close',array()),
            array('tablerow_close',array()),
            array('tablerow_open',array()),
            array('tablerow_close',array()),
            array('table_close',array(31)),
            array('p_open',array()),
            array('cdata',array('def')),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEquals($calls,array_map('stripbyteindex',$this->H->calls));
    }
    
    function testCellRowSpan() {
        $this->P->addMode('table',new Doku_Parser_Mode_Table());
        $this->P->parse('
abc
| a |  c:::||
|:::^ d  | e|
|b  ^  ::: |:::f|
def');
        
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n\nabc")),
            array('p_close',array()),
            array('table_open',array(3, 3, 6)),
            array('tablerow_open',array()),
            array('tablecell_open',array(1,NULL,2)),
            array('cdata',array(' a ')),
            array('tablecell_close',array()),
            array('tablecell_open',array(2,'right',1)),
            array('cdata',array('  c:::')),
            array('tablecell_close',array()),
            array('tablerow_close',array()),
            array('tablerow_open',array()),
            array('tableheader_open',array(1,'left',2)),
            array('cdata',array(' d  ')),
            array('tableheader_close',array()),
            array('tablecell_open',array(1,NULL,1)),
            array('cdata',array(' e')),
            array('tablecell_close',array()),
            array('tablerow_close',array()),
            array('tablerow_open',array()),
            array('tablecell_open',array(1,'left',1)),
            array('cdata',array('b  ')),
            array('tablecell_close',array()),
            array('tablecell_open',array(1,NULL,1)),
            array('cdata',array(':::f')),
            array('tablecell_close',array()),
            array('tablerow_close',array()),
            array('table_close',array(51)),
            array('p_open',array()),
            array('cdata',array('def')),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEquals($calls,array_map('stripbyteindex',$this->H->calls));
    }

    function testCellRowSpanFirstRow() {
        $this->P->addMode('table',new Doku_Parser_Mode_Table());
        $this->P->parse('
abc
|::: ^  d:::^:::|  :::  |
| b ^ e  | | ::: |
|c  ^  ::: | |:::|
def');

        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n\nabc")),
            array('p_close',array()),
            array('table_open',array(4, 3, 6)),
            array('tablerow_open',array()),
            array('tablecell_open',array(1,NULL,1)),
            array('cdata',array('')),
            array('tablecell_close',array()),
            array('tableheader_open',array(1,'right',1)),
            array('cdata',array('  d:::')),
            array('tableheader_close',array()),
            array('tableheader_open',array(1,NULL,1)),
            array('cdata',array('')),
            array('tableheader_close',array()),
            array('tablecell_open',array(1,NULL,3)),
            array('cdata',array('')),
            array('tablecell_close',array()),
            array('tablerow_close',array()),
            array('tablerow_open',array()),
            array('tablecell_open',array(1,NULL,1)),
            array('cdata',array(' b ')),
            array('tablecell_close',array()),
            array('tableheader_open',array(1,'left',2)),
            array('cdata',array(' e  ')),
            array('tableheader_close',array()),
            array('tablecell_open',array(1,NULL,1)),
            array('cdata',array(' ')),
            array('tablecell_close',array()),
            array('tablerow_close',array()),
            array('tablerow_open',array()),
            array('tablecell_open',array(1,'left',1)),
            array('cdata',array('c  ')),
            array('tablecell_close',array()),
            array('tablecell_open',array(1,NULL,1)),
            array('cdata',array(' ')),
            array('tablecell_close',array()),
            array('tablerow_close',array()),

            array('table_close',array(69)),
            array('p_open',array()),
            array('cdata',array('def')),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEquals($calls,array_map('stripbyteindex',$this->H->calls));
    }
    
    function testRowSpanTableHead() {
        $this->P->addMode('table',new Doku_Parser_Mode_Table());
        $this->P->parse('
abc
^ X1 ^ Y1 ^ Z1 ^
^ X2 ^ ::: ^ Z2 ^
| A3 | B3 | C3 |
def');

        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n\nabc")),
            array('p_close',array()),
            array('table_open',array(3, 3, 6)),
            array('tablethead_open',array()),
            array('tablerow_open',array()),
            array('tableheader_open',array(1,NULL,1)),
            array('cdata',array(' X1 ')),
            array('tableheader_close',array()),
            array('tableheader_open',array(1,NULL,2)),
            array('cdata',array(' Y1 ')),
            array('tableheader_close',array()),
            array('tableheader_open',array(1,NULL,1)),
            array('cdata',array(' Z1 ')),
            array('tableheader_close',array()),
            array('tablerow_close',array()),
            array('tablerow_open',array()),
            array('tableheader_open',array(1,NULL,1)),
            array('cdata',array(' X2 ')),
            array('tableheader_close',array()),
            array('tableheader_open',array(1,NULL,1)),
            array('cdata',array(' Z2 ')),
            array('tableheader_close',array()),
            array('tablerow_close',array()),
            array('tablethead_close',array()),
            array('tablerow_open',array()),
            array('tablecell_open',array(1,NULL,1)),
            array('cdata',array(' A3 ')),
            array('tablecell_close',array()),
            array('tablecell_open',array(1,NULL,1)),
            array('cdata',array(' B3 ')),
            array('tablecell_close',array()),
            array('tablecell_open',array(1,NULL,1)),
            array('cdata',array(' C3 ')),
            array('tablecell_close',array()),
            array('tablerow_close',array()),
            array('table_close',array(57)),
            array('p_open',array()),
            array('cdata',array('def')),
            array('p_close',array()),
            array('document_end',array()),
        );

        $this->assertEquals($calls,array_map('stripbyteindex',$this->H->calls));

    }

    function testRowSpanAcrossTableHeadBoundary() {
        $this->P->addMode('table',new Doku_Parser_Mode_Table());
        $this->P->parse('
abc
^ X1 ^ Y1 ^ Z1 ^
^ X2 ^ ::: ^ Z2 ^
| A3 | ::: | C3 |
| A4 | ::: | C4 |
def');

        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n\nabc")),
            array('p_close',array()),
            array('table_open',array(3, 4, 6)),
            array('tablethead_open',array()),
            array('tablerow_open',array()),
            array('tableheader_open',array(1,NULL,1)),
            array('cdata',array(' X1 ')),
            array('tableheader_close',array()),
            array('tableheader_open',array(1,NULL,2)),
            array('cdata',array(' Y1 ')),
            array('tableheader_close',array()),
            array('tableheader_open',array(1,NULL,1)),
            array('cdata',array(' Z1 ')),
            array('tableheader_close',array()),
            array('tablerow_close',array()),
            array('tablerow_open',array()),
            array('tableheader_open',array(1,NULL,1)),
            array('cdata',array(' X2 ')),
            array('tableheader_close',array()),
            array('tableheader_open',array(1,NULL,1)),
            array('cdata',array(' Z2 ')),
            array('tableheader_close',array()),
            array('tablerow_close',array()),
            array('tablethead_close',array()),
            array('tablerow_open',array()),
            array('tablecell_open',array(1,NULL,1)),
            array('cdata',array(' A3 ')),
            array('tablecell_close',array()),
            array('tablecell_open',array(1,NULL,2)),
            array('cdata',array('')),
            array('tablecell_close',array()),
            array('tablecell_open',array(1,NULL,1)),
            array('cdata',array(' C3 ')),
            array('tablecell_close',array()),
            array('tablerow_close',array()),
            array('tablerow_open',array()),
            array('tablecell_open',array(1,NULL,1)),
            array('cdata',array(' A4 ')),
            array('tablecell_close',array()),
            array('tablecell_open',array(1,NULL,1)),
            array('cdata',array(' C4 ')),
            array('tablecell_close',array()),
            array('tablerow_close',array()),
            array('table_close',array(76)),
            array('p_open',array()),
            array('cdata',array('def')),
            array('p_close',array()),
            array('document_end',array()),
        );

        $this->assertEquals($calls,array_map('stripbyteindex',$this->H->calls));

    }

    function testCellAlignmentFormatting() {
        $this->P->addMode('table',new Doku_Parser_Mode_Table());
        $this->P->addMode('strong',new Doku_Parser_Mode_Formatting('strong'));
        $this->P->parse('
abc
|  **X** | Y  ^  Z  |
def');
    
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n\nabc")),
            array('p_close',array()),
            array('table_open',array(3, 1, 6)),
            array('tablerow_open',array()),
            array('tablecell_open',array(1,'right',1)),
            array('cdata',array('  ')),
            array('strong_open',array()),
            array('cdata',array('X')),
            array('strong_close',array()),
            array('cdata',array(' ')),
            array('tablecell_close',array()),
            array('tablecell_open',array(1,'left',1)),
            array('cdata',array(' Y  ')),
            array('tablecell_close',array()),
            array('tableheader_open',array(1,'center',1)),
            array('cdata',array('  Z  ')),
            array('tableheader_close',array()),
            array('tablerow_close',array()),
            array('table_close',array(27)),
            array('p_open',array()),
            array('cdata',array('def')),
            array('p_close',array()),
            array('document_end',array()),
        );
 
        $this->assertEquals($calls,array_map('stripbyteindex',$this->H->calls));
        
    }
    
    function testTableEol() {
        $this->P->addMode('table',new Doku_Parser_Mode_Table());
        $this->P->addMode('eol',new Doku_Parser_Mode_Eol());
        $this->P->parse('
abc
| Row 0 Col 1    | Row 0 Col 2     | Row 0 Col 3        |
| Row 1 Col 1    | Row 1 Col 2     | Row 1 Col 3        |
def');
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("abc")),
            array('p_close',array()),
            array('table_open',array(3, 2, 6)),
            array('tablerow_open',array()),
            array('tablecell_open',array(1,'left',1)),
            array('cdata',array(' Row 0 Col 1    ')),
            array('tablecell_close',array()),
            array('tablecell_open',array(1,'left',1)),
            array('cdata',array(' Row 0 Col 2     ')),
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
            array('cdata',array(' Row 1 Col 2     ')),
            array('tablecell_close',array()),
            array('tablecell_open',array(1,'left',1)),
            array('cdata',array(' Row 1 Col 3        ')),
            array('tablecell_close',array()),
            array('tablerow_close',array()),
            array('table_close',array(121)),
            array('p_open',array()),
            array('cdata',array('def')),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEquals($calls,array_map('stripbyteindex',$this->H->calls));
    }
    
    // This is really a failing test - formatting able to spread across cols
    // Problem is fixing it would mean a major rewrite of table handling
    function testTableStrong() {
        $this->P->addMode('table',new Doku_Parser_Mode_Table());
        $this->P->addMode('strong',new Doku_Parser_Mode_Formatting('strong'));
        $this->P->parse('
abc
| **Row 0 Col 1**    | **Row 0 Col 2     | Row 0 Col 3**        |
| Row 1 Col 1    | Row 1 Col 2     | Row 1 Col 3        |
def');
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n\nabc")),
            array('p_close',array()),
            array('table_open',array(3, 2, 6)),
            array('tablerow_open',array()),
            array('tablecell_open',array(1,'left',1)),
            array('cdata',array(' ')),
            array('strong_open',array()),
            array('cdata',array('Row 0 Col 1')),
            array('strong_close',array()),
            array('cdata',array('    ')),
            array('tablecell_close',array()),
            array('tablecell_open',array(1,'left',1)),
            array('cdata',array(' ')),
            array('strong_open',array()),
            array('cdata',array('Row 0 Col 2     | Row 0 Col 3')),
            array('strong_close',array()),
            array('cdata',array('        ')),
            array('tablecell_close',array()),
            array('tablecell_open',array(1,null,1)),
            array('cdata',array('')),
            array('tablecell_close',array()),
            array('tablerow_close',array()),
            array('tablerow_open',array()),
            array('tablecell_open',array(1,'left',1)),
            array('cdata',array(' Row 1 Col 1    ')),
            array('tablecell_close',array()),
            array('tablecell_open',array(1,'left',1)),
            array('cdata',array(' Row 1 Col 2     ')),
            array('tablecell_close',array()),
            array('tablecell_open',array(1,'left',1)),
            array('cdata',array(' Row 1 Col 3        ')),
            array('tablecell_close',array()),
            array('tablerow_close',array()),
            array('table_close',array(129)),
            array('p_open',array()),
            array('cdata',array('def')),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEquals($calls,array_map('stripbyteindex',$this->H->calls));
    }
    
    // This is really a failing test - unformatted able to spread across cols
    // Problem is fixing it would mean a major rewrite of table handling
    function testTableUnformatted() {
        $this->P->addMode('table',new Doku_Parser_Mode_Table());
        $this->P->addMode('unformatted',new Doku_Parser_Mode_Unformatted());
        $this->P->parse('
abc
| <nowiki>Row 0 Col 1</nowiki>    | <nowiki>Row 0 Col 2     | Row 0 Col 3</nowiki>        |
| Row 1 Col 1    | Row 1 Col 2     | Row 1 Col 3        |
def');
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n\nabc")),
            array('p_close',array()),
            array('table_open',array(3, 2, 6)),
            array('tablerow_open',array()),
            array('tablecell_open',array(1,'left',1)),
            array('cdata',array(' ')),
            array('unformatted',array('Row 0 Col 1')),
            array('cdata',array('    ')),
            array('tablecell_close',array()),
            array('tablecell_open',array(1,'left',1)),
            array('cdata',array(' ')),
            array('unformatted',array('Row 0 Col 2     | Row 0 Col 3')),
            array('cdata',array('        ')),
            array('tablecell_close',array()),
            array('tablecell_open',array(1,null,1)),
            array('cdata',array('')),
            array('tablecell_close',array()),
            array('tablerow_close',array()),
            array('tablerow_open',array()),
            array('tablecell_open',array(1,'left',1)),
            array('cdata',array(' Row 1 Col 1    ')),
            array('tablecell_close',array()),
            array('tablecell_open',array(1,'left',1)),
            array('cdata',array(' Row 1 Col 2     ')),
            array('tablecell_close',array()),
            array('tablecell_open',array(1,'left',1)),
            array('cdata',array(' Row 1 Col 3        ')),
            array('tablecell_close',array()),
            array('tablerow_close',array()),
            array('table_close',array(155)),
            array('p_open',array()),
            array('cdata',array('def')),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEquals($calls,array_map('stripbyteindex',$this->H->calls));
    }
    
    function testTableLinebreak() {
        $this->P->addMode('table',new Doku_Parser_Mode_Table());
        $this->P->addMode('linebreak',new Doku_Parser_Mode_Linebreak());
        $this->P->parse('
abc
| Row 0\\\\ Col 1    | Row 0 Col 2     | Row 0 Col 3        |
| Row 1 Col 1    | Row 1 Col 2     | Row 1 Col 3        |
def');
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n\nabc")),
            array('p_close',array()),
            array('table_open',array(3, 2, 6)),
            array('tablerow_open',array()),
            array('tablecell_open',array(1,'left',1)),
            array('cdata',array(' Row 0')),
            array('linebreak',array()),
            array('cdata',array('Col 1    ')),
            array('tablecell_close',array()),
            array('tablecell_open',array(1,'left',1)),
            array('cdata',array(' Row 0 Col 2     ')),
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
            array('cdata',array(' Row 1 Col 2     ')),
            array('tablecell_close',array()),
            array('tablecell_open',array(1,'left',1)),
            array('cdata',array(' Row 1 Col 3        ')),
            array('tablecell_close',array()),
            array('tablerow_close',array()),
            array('table_close',array(123)),
            array('p_open',array()),
            array('cdata',array('def')),
            array('p_close',array()),
            array('document_end',array()),
        );

        $this->assertEquals($calls,array_map('stripbyteindex',$this->H->calls));
    }
    
    // This is really a failing test - footnote able to spread across cols
    // Problem is fixing it would mean a major rewrite of table handling
    function testTableFootnote() {
        $this->P->addMode('table',new Doku_Parser_Mode_Table());
        $this->P->addMode('footnote',new Doku_Parser_Mode_Footnote());
        $this->P->parse('
abc
| ((Row 0 Col 1))    | ((Row 0 Col 2     | Row 0 Col 3))        |
| Row 1 Col 1    | Row 1 Col 2     | Row 1 Col 3        |
def');
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n\nabc")),
            array('p_close',array()),
            array('table_open',array(3, 2, 6)),
            array('tablerow_open',array()),
            array('tablecell_open',array(1,'left',1)),
            array('cdata',array(' ')),
            array('nest', array ( array (
              array('footnote_open',array()),
              array('cdata',array('Row 0 Col 1')),
              array('footnote_close',array()),
            ))),
            array('cdata',array('    ')),
            array('tablecell_close',array()),
            array('tablecell_open',array(1,'left',1)),
            array('cdata',array(' ')),
            array('nest', array ( array (
              array('footnote_open',array()),
              array('cdata',array('Row 0 Col 2     | Row 0 Col 3')),
              array('footnote_close',array()),
            ))),
            array('cdata',array('        ')),
            array('tablecell_close',array()),
            array('tablecell_open',array(1,null,1)),
            array('cdata',array('')),
            array('tablecell_close',array()),
            array('tablerow_close',array()),
            array('tablerow_open',array()),
            array('tablecell_open',array(1,'left',1)),
            array('cdata',array(' Row 1 Col 1    ')),
            array('tablecell_close',array()),
            array('tablecell_open',array(1,'left',1)),
            array('cdata',array(' Row 1 Col 2     ')),
            array('tablecell_close',array()),
            array('tablecell_open',array(1,'left',1)),
            array('cdata',array(' Row 1 Col 3        ')),
            array('tablecell_close',array()),
            array('tablerow_close',array()),
            array('table_close',array(129)),
            array('p_open',array()),
            array('cdata',array('def')),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEquals($calls,array_map('stripbyteindex',$this->H->calls));
    }

    function testTable_FS1833() {
        $syntax = " \n| Row 0 Col 1    |\n";
        $this->P->addMode('table',new Doku_Parser_Mode_Table());
        $this->P->parse($syntax);
        $calls = array (
            array('document_start',array()),
            array('table_open',array(1, 1, 2)),
            array('tablerow_open',array()),
            array('tablecell_open',array(1,'left',1)),
            array('cdata',array(' Row 0 Col 1    ')),
            array('tablecell_close',array()),
            array('tablerow_close',array()),
            array('table_close',array(strlen($syntax))),
            array('document_end',array()),
        );
        $this->assertEquals(array_map('stripbyteindex',$this->H->calls),$calls);
    }

}
