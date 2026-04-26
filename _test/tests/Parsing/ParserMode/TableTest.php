<?php

namespace dokuwiki\test\Parsing\ParserMode;

use dokuwiki\Parsing\ParserMode\Eol;
use dokuwiki\Parsing\ParserMode\Footnote;
use dokuwiki\Parsing\ParserMode\Strong;
use dokuwiki\Parsing\ParserMode\Linebreak;
use dokuwiki\Parsing\ParserMode\Table;
use dokuwiki\Parsing\ParserMode\Unformatted;

class TableTest extends ParserTestBase
{

    function testTable() {
        $this->P->addMode('table',new Table());
        $this->P->parse('
abc
| Row 0 Col 1    | Row 0 Col 2     | Row 0 Col 3        |
| Row 1 Col 1    | Row 1 Col 2     | Row 1 Col 3        |
def');
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n\nabc"]],
            ['p_close',[]],
            ['table_open',[3, 2, 6]],
            ['tablerow_open',[]],
            ['tablecell_open',[1,'left',1]],
            ['cdata',[' Row 0 Col 1    ']],
            ['tablecell_close',[]],
            ['tablecell_open',[1,'left',1]],
            ['cdata',[' Row 0 Col 2     ']],
            ['tablecell_close',[]],
            ['tablecell_open',[1,'left',1]],
            ['cdata',[' Row 0 Col 3        ']],
            ['tablecell_close',[]],
            ['tablerow_close',[]],
            ['tablerow_open',[]],
            ['tablecell_open',[1,'left',1]],
            ['cdata',[' Row 1 Col 1    ']],
            ['tablecell_close',[]],
            ['tablecell_open',[1,'left',1]],
            ['cdata',[' Row 1 Col 2     ']],
            ['tablecell_close',[]],
            ['tablecell_open',[1,'left',1]],
            ['cdata',[' Row 1 Col 3        ']],
            ['tablecell_close',[]],
            ['tablerow_close',[]],
            ['table_close',[121]],
            ['p_open',[]],
            ['cdata',['def']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testTableWinEOL() {
        $this->P->addMode('table',new Table());
        $this->P->parse("\r\nabc\r\n| Row 0 Col 1    | Row 0 Col 2     | Row 0 Col 3        |\r\n| Row 1 Col 1    | Row 1 Col 2     | Row 1 Col 3        |\r\ndef");
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n\nabc"]],
            ['p_close',[]],
            ['table_open',[3, 2, 6]],
            ['tablerow_open',[]],
            ['tablecell_open',[1,'left',1]],
            ['cdata',[' Row 0 Col 1    ']],
            ['tablecell_close',[]],
            ['tablecell_open',[1,'left',1]],
            ['cdata',[' Row 0 Col 2     ']],
            ['tablecell_close',[]],
            ['tablecell_open',[1,'left',1]],
            ['cdata',[' Row 0 Col 3        ']],
            ['tablecell_close',[]],
            ['tablerow_close',[]],
            ['tablerow_open',[]],
            ['tablecell_open',[1,'left',1]],
            ['cdata',[' Row 1 Col 1    ']],
            ['tablecell_close',[]],
            ['tablecell_open',[1,'left',1]],
            ['cdata',[' Row 1 Col 2     ']],
            ['tablecell_close',[]],
            ['tablecell_open',[1,'left',1]],
            ['cdata',[' Row 1 Col 3        ']],
            ['tablecell_close',[]],
            ['tablerow_close',[]],
            ['table_close',[121]],
            ['p_open',[]],
            ['cdata',['def']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testEmptyTable() {
        $this->P->addMode('table',new Table());
        $this->P->parse('
abc
|
def');

        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n\nabc"]],
            ['p_close',[]],
            ['table_open',[0, 1, 6]],
            ['tablerow_open',[]],
            ['tablerow_close',[]],
            ['table_close',[7]],
            ['p_open',[]],
            ['cdata',['def']],
            ['p_close',[]],
            ['document_end',[]],
        ];

        $this->assertCalls($calls, $this->H->calls);
    }

    function testTableHeaders() {
        $this->P->addMode('table',new Table());
        $this->P->parse('
abc
^ X | Y ^ Z |
def');

        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n\nabc"]],
            ['p_close',[]],
            ['table_open',[3, 1, 6]],
            ['tablerow_open',[]],
            ['tableheader_open',[1,null,1]],
            ['cdata',[' X ']],
            ['tableheader_close',[]],
            ['tablecell_open',[1,null,1]],
            ['cdata',[' Y ']],
            ['tablecell_close',[]],
            ['tableheader_open',[1,null,1]],
            ['cdata',[' Z ']],
            ['tableheader_close',[]],
            ['tablerow_close',[]],
            ['table_close',[19]],
            ['p_open',[]],
            ['cdata',['def']],
            ['p_close',[]],
            ['document_end',[]],
        ];

        $this->assertCalls($calls, $this->H->calls);

    }

    function testTableHead() {
        $this->P->addMode('table',new Table());
        $this->P->parse('
abc
^ X ^ Y ^ Z ^
| x | y | z |
def');

        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n\nabc"]],
            ['p_close',[]],
            ['table_open',[3, 2, 6]],
            ['tablethead_open',[]],
            ['tablerow_open',[]],
            ['tableheader_open',[1,null,1]],
            ['cdata',[' X ']],
            ['tableheader_close',[]],
            ['tableheader_open',[1,null,1]],
            ['cdata',[' Y ']],
            ['tableheader_close',[]],
            ['tableheader_open',[1,null,1]],
            ['cdata',[' Z ']],
            ['tableheader_close',[]],
            ['tablerow_close',[]],
            ['tablethead_close',[]],
            ['tablerow_open',[]],
            ['tablecell_open',[1,null,1]],
            ['cdata',[' x ']],
            ['tablecell_close',[]],
            ['tablecell_open',[1,null,1]],
            ['cdata',[' y ']],
            ['tablecell_close',[]],
            ['tablecell_open',[1,null,1]],
            ['cdata',[' z ']],
            ['tablecell_close',[]],
            ['tablerow_close',[]],
            ['table_close',[33]],
            ['p_open',[]],
            ['cdata',['def']],
            ['p_close',[]],
            ['document_end',[]],
        ];

        $this->assertCalls($calls, $this->H->calls);

    }

    function testTableHeadOneRowTable() {
        $this->P->addMode('table',new Table());
        $this->P->parse('
abc
^ X ^ Y ^ Z ^
def');

        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n\nabc"]],
            ['p_close',[]],
            ['table_open',[3, 1, 6]],
            ['tablerow_open',[]],
            ['tableheader_open',[1,null,1]],
            ['cdata',[' X ']],
            ['tableheader_close',[]],
            ['tableheader_open',[1,null,1]],
            ['cdata',[' Y ']],
            ['tableheader_close',[]],
            ['tableheader_open',[1,null,1]],
            ['cdata',[' Z ']],
            ['tableheader_close',[]],
            ['tablerow_close',[]],
            ['table_close',[19]],
            ['p_open',[]],
            ['cdata',['def']],
            ['p_close',[]],
            ['document_end',[]],
        ];

        $this->assertCalls($calls, $this->H->calls);

    }

    function testTableHeadMultiline() {
        $this->P->addMode('table',new Table());
        $this->P->parse('
abc
^ X1 ^ Y1 ^ Z1 ^
^ X2 ^ Y2 ^ Z2 ^
| A | B | C |
def');

        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n\nabc"]],
            ['p_close',[]],
            ['table_open',[3, 3, 6]],
            ['tablethead_open',[]],
            ['tablerow_open',[]],
            ['tableheader_open',[1,null,1]],
            ['cdata',[' X1 ']],
            ['tableheader_close',[]],
            ['tableheader_open',[1,null,1]],
            ['cdata',[' Y1 ']],
            ['tableheader_close',[]],
            ['tableheader_open',[1,null,1]],
            ['cdata',[' Z1 ']],
            ['tableheader_close',[]],
            ['tablerow_close',[]],
            ['tablerow_open',[]],
            ['tableheader_open',[1,null,1]],
            ['cdata',[' X2 ']],
            ['tableheader_close',[]],
            ['tableheader_open',[1,null,1]],
            ['cdata',[' Y2 ']],
            ['tableheader_close',[]],
            ['tableheader_open',[1,null,1]],
            ['cdata',[' Z2 ']],
            ['tableheader_close',[]],
            ['tablerow_close',[]],
            ['tablethead_close',[]],
            ['tablerow_open',[]],
            ['tablecell_open',[1,null,1]],
            ['cdata',[' A ']],
            ['tablecell_close',[]],
            ['tablecell_open',[1,null,1]],
            ['cdata',[' B ']],
            ['tablecell_close',[]],
            ['tablecell_open',[1,null,1]],
            ['cdata',[' C ']],
            ['tablecell_close',[]],
            ['tablerow_close',[]],
            ['table_close',[53]],
            ['p_open',[]],
            ['cdata',['def']],
            ['p_close',[]],
            ['document_end',[]],
        ];

        $this->assertCalls($calls, $this->H->calls);

    }

    function testCellAlignment() {
        $this->P->addMode('table',new Table());
        $this->P->parse('
abc
|  X | Y  ^  Z  |
def');

        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n\nabc"]],
            ['p_close',[]],
            ['table_open',[3, 1, 6]],
            ['tablerow_open',[]],
            ['tablecell_open',[1,'right',1]],
            ['cdata',['  X ']],
            ['tablecell_close',[]],
            ['tablecell_open',[1,'left',1]],
            ['cdata',[' Y  ']],
            ['tablecell_close',[]],
            ['tableheader_open',[1,'center',1]],
            ['cdata',['  Z  ']],
            ['tableheader_close',[]],
            ['tablerow_close',[]],
            ['table_close',[23]],
            ['p_open',[]],
            ['cdata',['def']],
            ['p_close',[]],
            ['document_end',[]],
        ];

        $this->assertCalls($calls, $this->H->calls);
    }

    function testCellSpan() {
        $this->P->addMode('table',new Table());
        $this->P->parse('
abc
|  d || e |
| f ^ ^|
||||
def');

        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n\nabc"]],
            ['p_close',[]],
            ['table_open',[3, 3, 6]],
            ['tablerow_open',[]],
            ['tablecell_open',[2,'right',1]],
            ['cdata',['  d ']],
            ['tablecell_close',[]],
            ['tablecell_open',[1,null,1]],
            ['cdata',[' e ']],
            ['tablecell_close',[]],
            ['tablerow_close',[]],
            ['tablerow_open',[]],
            ['tablecell_open',[1,null,1]],
            ['cdata',[' f ']],
            ['tablecell_close',[]],
            ['tableheader_open',[2,null,1]],
            ['cdata',[' ']],
            ['tableheader_close',[]],
            ['tablerow_close',[]],
            ['tablerow_open',[]],
            ['tablerow_close',[]],
            ['table_close',[31]],
            ['p_open',[]],
            ['cdata',['def']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testCellRowSpan() {
        $this->P->addMode('table',new Table());
        $this->P->parse('
abc
| a |  c:::||
|:::^ d  | e|
|b  ^  ::: |:::f|
def');

        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n\nabc"]],
            ['p_close',[]],
            ['table_open',[3, 3, 6]],
            ['tablerow_open',[]],
            ['tablecell_open',[1,null,2]],
            ['cdata',[' a ']],
            ['tablecell_close',[]],
            ['tablecell_open',[2,'right',1]],
            ['cdata',['  c:::']],
            ['tablecell_close',[]],
            ['tablerow_close',[]],
            ['tablerow_open',[]],
            ['tableheader_open',[1,'left',2]],
            ['cdata',[' d  ']],
            ['tableheader_close',[]],
            ['tablecell_open',[1,null,1]],
            ['cdata',[' e']],
            ['tablecell_close',[]],
            ['tablerow_close',[]],
            ['tablerow_open',[]],
            ['tablecell_open',[1,'left',1]],
            ['cdata',['b  ']],
            ['tablecell_close',[]],
            ['tablecell_open',[1,null,1]],
            ['cdata',[':::f']],
            ['tablecell_close',[]],
            ['tablerow_close',[]],
            ['table_close',[51]],
            ['p_open',[]],
            ['cdata',['def']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testCellRowSpanFirstRow() {
        $this->P->addMode('table',new Table());
        $this->P->parse('
abc
|::: ^  d:::^:::|  :::  |
| b ^ e  | | ::: |
|c  ^  ::: | |:::|
def');

        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n\nabc"]],
            ['p_close',[]],
            ['table_open',[4, 3, 6]],
            ['tablerow_open',[]],
            ['tablecell_open',[1,null,1]],
            ['cdata',['']],
            ['tablecell_close',[]],
            ['tableheader_open',[1,'right',1]],
            ['cdata',['  d:::']],
            ['tableheader_close',[]],
            ['tableheader_open',[1,null,1]],
            ['cdata',['']],
            ['tableheader_close',[]],
            ['tablecell_open',[1,null,3]],
            ['cdata',['']],
            ['tablecell_close',[]],
            ['tablerow_close',[]],
            ['tablerow_open',[]],
            ['tablecell_open',[1,null,1]],
            ['cdata',[' b ']],
            ['tablecell_close',[]],
            ['tableheader_open',[1,'left',2]],
            ['cdata',[' e  ']],
            ['tableheader_close',[]],
            ['tablecell_open',[1,null,1]],
            ['cdata',[' ']],
            ['tablecell_close',[]],
            ['tablerow_close',[]],
            ['tablerow_open',[]],
            ['tablecell_open',[1,'left',1]],
            ['cdata',['c  ']],
            ['tablecell_close',[]],
            ['tablecell_open',[1,null,1]],
            ['cdata',[' ']],
            ['tablecell_close',[]],
            ['tablerow_close',[]],

            ['table_close',[69]],
            ['p_open',[]],
            ['cdata',['def']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testRowSpanTableHead() {
        $this->P->addMode('table',new Table());
        $this->P->parse('
abc
^ X1 ^ Y1 ^ Z1 ^
^ X2 ^ ::: ^ Z2 ^
| A3 | B3 | C3 |
def');

        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n\nabc"]],
            ['p_close',[]],
            ['table_open',[3, 3, 6]],
            ['tablethead_open',[]],
            ['tablerow_open',[]],
            ['tableheader_open',[1,null,1]],
            ['cdata',[' X1 ']],
            ['tableheader_close',[]],
            ['tableheader_open',[1,null,2]],
            ['cdata',[' Y1 ']],
            ['tableheader_close',[]],
            ['tableheader_open',[1,null,1]],
            ['cdata',[' Z1 ']],
            ['tableheader_close',[]],
            ['tablerow_close',[]],
            ['tablerow_open',[]],
            ['tableheader_open',[1,null,1]],
            ['cdata',[' X2 ']],
            ['tableheader_close',[]],
            ['tableheader_open',[1,null,1]],
            ['cdata',[' Z2 ']],
            ['tableheader_close',[]],
            ['tablerow_close',[]],
            ['tablethead_close',[]],
            ['tablerow_open',[]],
            ['tablecell_open',[1,null,1]],
            ['cdata',[' A3 ']],
            ['tablecell_close',[]],
            ['tablecell_open',[1,null,1]],
            ['cdata',[' B3 ']],
            ['tablecell_close',[]],
            ['tablecell_open',[1,null,1]],
            ['cdata',[' C3 ']],
            ['tablecell_close',[]],
            ['tablerow_close',[]],
            ['table_close',[57]],
            ['p_open',[]],
            ['cdata',['def']],
            ['p_close',[]],
            ['document_end',[]],
        ];

        $this->assertCalls($calls, $this->H->calls);

    }

    function testRowSpanAcrossTableHeadBoundary() {
        $this->P->addMode('table',new Table());
        $this->P->parse('
abc
^ X1 ^ Y1 ^ Z1 ^
^ X2 ^ ::: ^ Z2 ^
| A3 | ::: | C3 |
| A4 | ::: | C4 |
def');

        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n\nabc"]],
            ['p_close',[]],
            ['table_open',[3, 4, 6]],
            ['tablethead_open',[]],
            ['tablerow_open',[]],
            ['tableheader_open',[1,null,1]],
            ['cdata',[' X1 ']],
            ['tableheader_close',[]],
            ['tableheader_open',[1,null,2]],
            ['cdata',[' Y1 ']],
            ['tableheader_close',[]],
            ['tableheader_open',[1,null,1]],
            ['cdata',[' Z1 ']],
            ['tableheader_close',[]],
            ['tablerow_close',[]],
            ['tablerow_open',[]],
            ['tableheader_open',[1,null,1]],
            ['cdata',[' X2 ']],
            ['tableheader_close',[]],
            ['tableheader_open',[1,null,1]],
            ['cdata',[' Z2 ']],
            ['tableheader_close',[]],
            ['tablerow_close',[]],
            ['tablethead_close',[]],
            ['tablerow_open',[]],
            ['tablecell_open',[1,null,1]],
            ['cdata',[' A3 ']],
            ['tablecell_close',[]],
            ['tablecell_open',[1,null,2]],
            ['cdata',['']],
            ['tablecell_close',[]],
            ['tablecell_open',[1,null,1]],
            ['cdata',[' C3 ']],
            ['tablecell_close',[]],
            ['tablerow_close',[]],
            ['tablerow_open',[]],
            ['tablecell_open',[1,null,1]],
            ['cdata',[' A4 ']],
            ['tablecell_close',[]],
            ['tablecell_open',[1,null,1]],
            ['cdata',[' C4 ']],
            ['tablecell_close',[]],
            ['tablerow_close',[]],
            ['table_close',[76]],
            ['p_open',[]],
            ['cdata',['def']],
            ['p_close',[]],
            ['document_end',[]],
        ];

        $this->assertCalls($calls, $this->H->calls);

    }

    function testCellAlignmentFormatting() {
        $this->P->addMode('table',new Table());
        $this->P->addMode('strong',new Strong());
        $this->P->parse('
abc
|  **X** | Y  ^  Z  |
def');

        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n\nabc"]],
            ['p_close',[]],
            ['table_open',[3, 1, 6]],
            ['tablerow_open',[]],
            ['tablecell_open',[1,'right',1]],
            ['cdata',['  ']],
            ['strong_open',[]],
            ['cdata',['X']],
            ['strong_close',[]],
            ['cdata',[' ']],
            ['tablecell_close',[]],
            ['tablecell_open',[1,'left',1]],
            ['cdata',[' Y  ']],
            ['tablecell_close',[]],
            ['tableheader_open',[1,'center',1]],
            ['cdata',['  Z  ']],
            ['tableheader_close',[]],
            ['tablerow_close',[]],
            ['table_close',[27]],
            ['p_open',[]],
            ['cdata',['def']],
            ['p_close',[]],
            ['document_end',[]],
        ];

        $this->assertCalls($calls, $this->H->calls);

    }

    function testTableEol() {
        $this->P->addMode('table',new Table());
        $this->P->addMode('eol',new Eol());
        $this->P->parse('
abc
| Row 0 Col 1    | Row 0 Col 2     | Row 0 Col 3        |
| Row 1 Col 1    | Row 1 Col 2     | Row 1 Col 3        |
def');
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["abc"]],
            ['p_close',[]],
            ['table_open',[3, 2, 6]],
            ['tablerow_open',[]],
            ['tablecell_open',[1,'left',1]],
            ['cdata',[' Row 0 Col 1    ']],
            ['tablecell_close',[]],
            ['tablecell_open',[1,'left',1]],
            ['cdata',[' Row 0 Col 2     ']],
            ['tablecell_close',[]],
            ['tablecell_open',[1,'left',1]],
            ['cdata',[' Row 0 Col 3        ']],
            ['tablecell_close',[]],
            ['tablerow_close',[]],
            ['tablerow_open',[]],
            ['tablecell_open',[1,'left',1]],
            ['cdata',[' Row 1 Col 1    ']],
            ['tablecell_close',[]],
            ['tablecell_open',[1,'left',1]],
            ['cdata',[' Row 1 Col 2     ']],
            ['tablecell_close',[]],
            ['tablecell_open',[1,'left',1]],
            ['cdata',[' Row 1 Col 3        ']],
            ['tablecell_close',[]],
            ['tablerow_close',[]],
            ['table_close',[121]],
            ['p_open',[]],
            ['cdata',['def']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    // This is really a failing test - formatting able to spread across cols
    // Problem is fixing it would mean a major rewrite of table handling
    function testTableStrong() {
        $this->P->addMode('table',new Table());
        $this->P->addMode('strong',new Strong());
        $this->P->parse('
abc
| **Row 0 Col 1**    | **Row 0 Col 2     | Row 0 Col 3**        |
| Row 1 Col 1    | Row 1 Col 2     | Row 1 Col 3        |
def');
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n\nabc"]],
            ['p_close',[]],
            ['table_open',[3, 2, 6]],
            ['tablerow_open',[]],
            ['tablecell_open',[1,'left',1]],
            ['cdata',[' ']],
            ['strong_open',[]],
            ['cdata',['Row 0 Col 1']],
            ['strong_close',[]],
            ['cdata',['    ']],
            ['tablecell_close',[]],
            ['tablecell_open',[1,'left',1]],
            ['cdata',[' ']],
            ['strong_open',[]],
            ['cdata',['Row 0 Col 2     | Row 0 Col 3']],
            ['strong_close',[]],
            ['cdata',['        ']],
            ['tablecell_close',[]],
            ['tablecell_open',[1,null,1]],
            ['cdata',['']],
            ['tablecell_close',[]],
            ['tablerow_close',[]],
            ['tablerow_open',[]],
            ['tablecell_open',[1,'left',1]],
            ['cdata',[' Row 1 Col 1    ']],
            ['tablecell_close',[]],
            ['tablecell_open',[1,'left',1]],
            ['cdata',[' Row 1 Col 2     ']],
            ['tablecell_close',[]],
            ['tablecell_open',[1,'left',1]],
            ['cdata',[' Row 1 Col 3        ']],
            ['tablecell_close',[]],
            ['tablerow_close',[]],
            ['table_close',[129]],
            ['p_open',[]],
            ['cdata',['def']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    // This is really a failing test - unformatted able to spread across cols
    // Problem is fixing it would mean a major rewrite of table handling
    function testTableUnformatted() {
        $this->P->addMode('table',new Table());
        $this->P->addMode('unformatted',new Unformatted());
        $this->P->parse('
abc
| <nowiki>Row 0 Col 1</nowiki>    | <nowiki>Row 0 Col 2     | Row 0 Col 3</nowiki>        |
| Row 1 Col 1    | Row 1 Col 2     | Row 1 Col 3        |
def');
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n\nabc"]],
            ['p_close',[]],
            ['table_open',[3, 2, 6]],
            ['tablerow_open',[]],
            ['tablecell_open',[1,'left',1]],
            ['cdata',[' ']],
            ['unformatted',['Row 0 Col 1']],
            ['cdata',['    ']],
            ['tablecell_close',[]],
            ['tablecell_open',[1,'left',1]],
            ['cdata',[' ']],
            ['unformatted',['Row 0 Col 2     | Row 0 Col 3']],
            ['cdata',['        ']],
            ['tablecell_close',[]],
            ['tablecell_open',[1,null,1]],
            ['cdata',['']],
            ['tablecell_close',[]],
            ['tablerow_close',[]],
            ['tablerow_open',[]],
            ['tablecell_open',[1,'left',1]],
            ['cdata',[' Row 1 Col 1    ']],
            ['tablecell_close',[]],
            ['tablecell_open',[1,'left',1]],
            ['cdata',[' Row 1 Col 2     ']],
            ['tablecell_close',[]],
            ['tablecell_open',[1,'left',1]],
            ['cdata',[' Row 1 Col 3        ']],
            ['tablecell_close',[]],
            ['tablerow_close',[]],
            ['table_close',[155]],
            ['p_open',[]],
            ['cdata',['def']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testTableLinebreak() {
        $this->P->addMode('table',new Table());
        $this->P->addMode('linebreak',new Linebreak());
        $this->P->parse('
abc
| Row 0\\\\ Col 1    | Row 0 Col 2     | Row 0 Col 3        |
| Row 1 Col 1    | Row 1 Col 2     | Row 1 Col 3        |
def');
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n\nabc"]],
            ['p_close',[]],
            ['table_open',[3, 2, 6]],
            ['tablerow_open',[]],
            ['tablecell_open',[1,'left',1]],
            ['cdata',[' Row 0']],
            ['linebreak',[]],
            ['cdata',['Col 1    ']],
            ['tablecell_close',[]],
            ['tablecell_open',[1,'left',1]],
            ['cdata',[' Row 0 Col 2     ']],
            ['tablecell_close',[]],
            ['tablecell_open',[1,'left',1]],
            ['cdata',[' Row 0 Col 3        ']],
            ['tablecell_close',[]],
            ['tablerow_close',[]],
            ['tablerow_open',[]],
            ['tablecell_open',[1,'left',1]],
            ['cdata',[' Row 1 Col 1    ']],
            ['tablecell_close',[]],
            ['tablecell_open',[1,'left',1]],
            ['cdata',[' Row 1 Col 2     ']],
            ['tablecell_close',[]],
            ['tablecell_open',[1,'left',1]],
            ['cdata',[' Row 1 Col 3        ']],
            ['tablecell_close',[]],
            ['tablerow_close',[]],
            ['table_close',[123]],
            ['p_open',[]],
            ['cdata',['def']],
            ['p_close',[]],
            ['document_end',[]],
        ];

        $this->assertCalls($calls, $this->H->calls);
    }

    // This is really a failing test - footnote able to spread across cols
    // Problem is fixing it would mean a major rewrite of table handling
    function testTableFootnote() {
        $this->P->addMode('table',new Table());
        $this->P->addMode('footnote',new Footnote());
        $this->P->parse('
abc
| ((Row 0 Col 1))    | ((Row 0 Col 2     | Row 0 Col 3))        |
| Row 1 Col 1    | Row 1 Col 2     | Row 1 Col 3        |
def');
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n\nabc"]],
            ['p_close',[]],
            ['table_open',[3, 2, 6]],
            ['tablerow_open',[]],
            ['tablecell_open',[1,'left',1]],
            ['cdata',[' ']],
            ['nest', [ [
              ['footnote_open',[]],
              ['cdata',['Row 0 Col 1']],
              ['footnote_close',[]],
            ]]],
            ['cdata',['    ']],
            ['tablecell_close',[]],
            ['tablecell_open',[1,'left',1]],
            ['cdata',[' ']],
            ['nest', [ [
              ['footnote_open',[]],
              ['cdata',['Row 0 Col 2     | Row 0 Col 3']],
              ['footnote_close',[]],
            ]]],
            ['cdata',['        ']],
            ['tablecell_close',[]],
            ['tablecell_open',[1,null,1]],
            ['cdata',['']],
            ['tablecell_close',[]],
            ['tablerow_close',[]],
            ['tablerow_open',[]],
            ['tablecell_open',[1,'left',1]],
            ['cdata',[' Row 1 Col 1    ']],
            ['tablecell_close',[]],
            ['tablecell_open',[1,'left',1]],
            ['cdata',[' Row 1 Col 2     ']],
            ['tablecell_close',[]],
            ['tablecell_open',[1,'left',1]],
            ['cdata',[' Row 1 Col 3        ']],
            ['tablecell_close',[]],
            ['tablerow_close',[]],
            ['table_close',[129]],
            ['p_open',[]],
            ['cdata',['def']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testTable_FS1833() {
        $syntax = " \n| Row 0 Col 1    |\n";
        $this->P->addMode('table',new Table());
        $this->P->parse($syntax);
        $calls = [
            ['document_start',[]],
            ['table_open',[1, 1, 2]],
            ['tablerow_open',[]],
            ['tablecell_open',[1,'left',1]],
            ['cdata',[' Row 0 Col 1    ']],
            ['tablecell_close',[]],
            ['tablerow_close',[]],
            ['table_close',[strlen($syntax)]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    /**
     * missing cells in one row get filled up...
     */
    function testTable_CellFix() {
        $syntax = "\n| r1c1 | r1c2 | r1c3 |\n| r2c1 |\n";
        $this->P->addMode('table',new Table());
        $this->P->parse($syntax);
        $calls = [
            ['document_start',[]],
            ['table_open',[3, 2, 2]],

            ['tablerow_open',[]],
            ['tablecell_open',[1,null,1]],
            ['cdata',[' r1c1 ']],
            ['tablecell_close',[]],
            ['tablecell_open',[1,null,1]],
            ['cdata',[' r1c2 ']],
            ['tablecell_close',[]],
            ['tablecell_open',[1,null,1]],
            ['cdata',[' r1c3 ']],
            ['tablecell_close',[]],
            ['tablerow_close',[]],

            ['tablerow_open',[]],
            ['tablecell_open',[1,null,1]],
            ['cdata',[' r2c1 ']],
            ['tablecell_close',[]],
            ['tablecell_open',[1,null,1]],
            ['cdata',['']],
            ['tablecell_close',[]],
            ['tablecell_open',[1,null,1]],
            ['cdata',['']],
            ['tablecell_close',[]],
            ['tablerow_close',[]],

            ['table_close',[strlen($syntax)]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    /**
     * ... even if the longer row comes later
     */
    function testTable_CellFix2() {
        $syntax = "\n| r1c1 |\n| r2c1 | r2c2 | r2c3 |\n";
        $this->P->addMode('table',new Table());
        $this->P->parse($syntax);
        $calls = [
            ['document_start',[]],
            ['table_open',[3, 2, 2]],

            ['tablerow_open',[]],
            ['tablecell_open',[1,null,1]],
            ['cdata',[' r1c1 ']],
            ['tablecell_close',[]],
            ['tablecell_open',[1,null,1]],
            ['cdata',['']],
            ['tablecell_close',[]],
            ['tablecell_open',[1,null,1]],
            ['cdata',['']],
            ['tablecell_close',[]],
            ['tablerow_close',[]],

            ['tablerow_open',[]],
            ['tablecell_open',[1,null,1]],
            ['cdata',[' r2c1 ']],
            ['tablecell_close',[]],
            ['tablecell_open',[1,null,1]],
            ['cdata',[' r2c2 ']],
            ['tablecell_close',[]],
            ['tablecell_open',[1,null,1]],
            ['cdata',[' r2c3 ']],
            ['tablecell_close',[]],
            ['tablerow_close',[]],

            ['table_close',[strlen($syntax)]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }
}
