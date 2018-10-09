<?php

require_once DOKU_INC.'inc/DifferenceEngine.php';

/**
 * Class difference_engine_test
 */
class difference_engine_test extends DokuWikiTest {
    public $x = "zzz\n\naaa\n\nbbb\n\nccc\n\nddd\n\nddd\n\nddd\n\neee\n\nfff";
    public $y = "ddd\n\naaa\n\nbbb\n\nbbb\n\nccc\n\nccc\n\neee";

    function test_render_table(){
        $diff = new Diff(explode("\n", $this->x), explode("\n", $this->y));
        $diffformatter = new TableDiffFormatter();
        $actual = $diffformatter->format($diff);
        $expected = '<tr><td class="diff-blockheader" colspan="2">Line 1:</td>
<td class="diff-blockheader" colspan="2">Line 1:</td>
</tr>
<tr><td class="diff-lineheader">-</td><td class="diff-deletedline"><strong class="diff-mark">zzz</strong></td><td class="diff-lineheader">+</td><td class="diff-addedline"><strong class="diff-mark">ddd</strong></td></tr>
<tr><td class="diff-lineheader">&#160;</td><td class="diff-context"></td><td class="diff-lineheader">&#160;</td><td class="diff-context"></td></tr>
<tr><td class="diff-lineheader">&#160;</td><td class="diff-context">aaa</td><td class="diff-lineheader">&#160;</td><td class="diff-context">aaa</td></tr>
<tr><td colspan="2">&#160;</td><td class="diff-lineheader">+</td><td class="diff-addedline"></td></tr>
<tr><td colspan="2">&#160;</td><td class="diff-lineheader">+</td><td class="diff-addedline">bbb</td></tr>
<tr><td class="diff-lineheader">&#160;</td><td class="diff-context"></td><td class="diff-lineheader">&#160;</td><td class="diff-context"></td></tr>
<tr><td class="diff-lineheader">&#160;</td><td class="diff-context">bbb</td><td class="diff-lineheader">&#160;</td><td class="diff-context">bbb</td></tr>
<tr><td class="diff-blockheader" colspan="2">Line 7:</td>
<td class="diff-blockheader" colspan="2">Line 9:</td>
</tr>
<tr><td class="diff-lineheader">&#160;</td><td class="diff-context">ccc</td><td class="diff-lineheader">&#160;</td><td class="diff-context">ccc</td></tr>
<tr><td class="diff-lineheader">&#160;</td><td class="diff-context"></td><td class="diff-lineheader">&#160;</td><td class="diff-context"></td></tr>
<tr><td class="diff-lineheader">-</td><td class="diff-deletedline"><strong class="diff-mark">ddd </strong></td><td class="diff-lineheader">+</td><td class="diff-addedline"><strong class="diff-mark">ccc</strong></td></tr>
<tr><td class="diff-lineheader">-</td><td class="diff-deletedline"><strong class="diff-mark"> </strong></td><td class="diff-lineheader">+</td><td class="diff-addedline"></td></tr>
<tr><td class="diff-lineheader">-</td><td class="diff-deletedline"><strong class="diff-mark">ddd </strong></td><td class="diff-lineheader">+</td><td class="diff-addedline"></td></tr>
<tr><td class="diff-lineheader">-</td><td class="diff-deletedline"><strong class="diff-mark"> </strong></td><td class="diff-lineheader">+</td><td class="diff-addedline"></td></tr>
<tr><td class="diff-lineheader">-</td><td class="diff-deletedline"><strong class="diff-mark">ddd</strong></td><td class="diff-lineheader">+</td><td class="diff-addedline"></td></tr>
<tr><td class="diff-lineheader">&#160;</td><td class="diff-context"></td><td class="diff-lineheader">&#160;</td><td class="diff-context"></td></tr>
<tr><td class="diff-lineheader">&#160;</td><td class="diff-context">eee</td><td class="diff-lineheader">&#160;</td><td class="diff-context">eee</td></tr>
<tr><td class="diff-lineheader">-</td><td class="diff-deletedline"></td><td colspan="2">&#160;</td></tr>
<tr><td class="diff-lineheader">-</td><td class="diff-deletedline">fff</td><td colspan="2">&#160;</td></tr>
';
        $this->assertEquals($expected, $actual);
    }

    function test_render_inline(){
        $diff = new Diff(explode("\n", $this->x), explode("\n", $this->y));
        $diffformatter = new InlineDiffFormatter();
        $actual = $diffformatter->format($diff);
        $expected = '<tr><td colspan="2" class="diff-blockheader">@@ Line -1,5 +1,7 @@&#160;<span class="diff-deletedline"><del>removed</del></span>&#160;<span class="diff-addedline">created</span></td></tr>

<tr><td class="diff-lineheader">&#160;</td><td><span class="diff-deletedline"><del>zzz</del></span><span class="diff-addedline">ddd</span></td></tr>
<tr><td class="diff-lineheader">&#160;</td><td class="diff-context"></td></tr>
<tr><td class="diff-lineheader">&#160;</td><td class="diff-context">aaa</td></tr>
<tr><td class="diff-lineheader">&#160;</td><td class="diff-addedline"></td></tr>
<tr><td class="diff-lineheader">&#160;</td><td class="diff-addedline">bbb</td></tr>
<tr><td class="diff-lineheader">&#160;</td><td class="diff-context"></td></tr>
<tr><td class="diff-lineheader">&#160;</td><td class="diff-context">bbb</td></tr>
<tr><td colspan="2" class="diff-blockheader">@@ Line -7,11 +9,5 @@&#160;<span class="diff-deletedline"><del>removed</del></span>&#160;<span class="diff-addedline">created</span></td></tr>

<tr><td class="diff-lineheader">&#160;</td><td class="diff-context">ccc</td></tr>
<tr><td class="diff-lineheader">&#160;</td><td class="diff-context"></td></tr>
<tr><td class="diff-lineheader">&#160;</td><td><span class="diff-deletedline"><del>ddd </del></span></td></tr>
<tr><td class="diff-lineheader">&#160;</td><td><span class="diff-deletedline"><del> </del></span></td></tr>
<tr><td class="diff-lineheader">&#160;</td><td><span class="diff-deletedline"><del>ddd </del></span></td></tr>
<tr><td class="diff-lineheader">&#160;</td><td><span class="diff-deletedline"><del> </del></span></td></tr>
<tr><td class="diff-lineheader">&#160;</td><td><span class="diff-deletedline"><del>ddd</del></span><span class="diff-addedline">ccc</span></td></tr>
<tr><td class="diff-lineheader">&#160;</td><td class="diff-context"></td></tr>
<tr><td class="diff-lineheader">&#160;</td><td class="diff-context">eee</td></tr>
<tr><td class="diff-lineheader">&#160;</td><td class="diff-deletedline"><del></del></td></tr>
<tr><td class="diff-lineheader">&#160;</td><td class="diff-deletedline"><del>fff</del></td></tr>
';
        $this->assertEquals($expected, $actual);
    }

    function test_engine_diag(){
        // initialize
        $eng = new _DiffEngine;
        $eng->diff(explode("\n", $this->x), explode("\n", $this->y));
        // check
        $this->assertEquals(
            array(9, array(array(0,0),array(1,2),array(3,4),array(4,5),array(5,7),array(6,9),array(7,10),array(9,12),array(15,13))),
            $eng->_diag(0, 15, 0, 13, 8)
        );
    }
}
//Setup VIM: ex: et ts=4 :
