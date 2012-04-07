<?php
require_once DOKU_INC.'inc/DifferenceEngine.php';

class differenceengine_test extends UnitTestCase {

    function test_white_between_words(){
        // From FS#2161
        global $lang;

        $df = new Diff(explode("\n","example"),
                       explode("\n","example example2"));

        $idf = new InlineDiffFormatter();
        $tdf = new TableDiffFormatter();

        $this->assertEqual($idf->format($df), '<tr><td colspan="4" class="diff-blockheader">@@ ' . $lang['line'] .
                                              ' -1 +1 @@&nbsp;<span class="diff-deletedline"><del>' . $lang['deleted'] .
                                              '</del></span>&nbsp;<span class="diff-addedline">' . $lang['created'] .
                                              '</span></td></tr>

<tr><td colspan="4">example&nbsp;<span class="diff-addedline">example2</span></td></tr>
');
        $this->assertEqual($tdf->format($df),
                           '<tr><td class="diff-blockheader" colspan="2">' . $lang['line'] . ' 1:</td>
<td class="diff-blockheader" colspan="2">' . $lang['line'] . ' 1:</td>
</tr>
<tr><td>-</td><td class="diff-deletedline">example</td><td>+</td><td class="diff-addedline">example&nbsp;<strong>example2</strong></td></tr>
');
    }
}
//Setup VIM: ex: et ts=4 :
