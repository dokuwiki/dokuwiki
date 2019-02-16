<?php

class styleutils_cssstyleini_test extends DokuWikiTest {

    function test_styleini()
    {
        $tpl = 'dokuwiki';
        $util = new \dokuwiki\StyleUtils;

        $old = $util->cssStyleiniOld($tpl);
        $new = $util->cssStyleini($tpl);

        $this->assertEquals($old, $new);

    }

}
