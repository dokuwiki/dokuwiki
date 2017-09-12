<?php

class html_revision_link extends DokuWikiTest {

    function testNormalLink() {
        global $conf;

        $conf['rev_handle'] = 'normal';
        $conf['userewrite']  = 0; //to have predictable \wl output

        $rev = 1385051947;
        $id = ':wiki:syntax';

        $this->assertEquals(
            DOKU_BASE.DOKU_SCRIPT.'?id='.$id.'&rev='.$rev,
            html_revision_link($id, $rev)
        );
    }

    function testOnlyMediaLink() {
        global $conf;

        $conf['rev_handle'] = 'only_media';
        $conf['userewrite']  = 0; //to have predictable \wl output

        $rev = 1385051947;
        $id = ':wiki:syntax';

        $this->assertEquals(
            DOKU_BASE.DOKU_SCRIPT.'?id='.$id.'&rev='.$rev,
            html_revision_link($id, $rev)
        );
    }

    function testAtLink() {
        global $conf;

        $conf['rev_handle'] = 'at';
        $conf['userewrite']  = 0; //to have predictable \wl output

        $rev = 1385051947;
        $id = ':wiki:syntax';

        $this->assertEquals(
            DOKU_BASE.DOKU_SCRIPT.'?id='.$id.'&at='.$rev,
            html_revision_link($id, $rev)
        );
    }
}
