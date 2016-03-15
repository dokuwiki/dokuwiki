<?php

class common_saveWikiText_test extends DokuWikiTest {

    /**
     * Execute a whole bunch of saves on the same page and check the results
     */
    function test_savesequence() {
        global $REV;

        $page = 'page';
        $file = wikiFN($page);

        // create the page
        $this->assertFileNotExists($file);
        saveWikiText($page, 'teststring', 'first save', false);
        $this->assertFileExists($file);
        $lastmod = filemtime($file);

        $pagelog = new PageChangeLog($page);
        $revisions = $pagelog->getRevisions(-1, 200);
        $this->assertEquals(1, count($revisions));
        $revinfo = $pagelog->getRevisionInfo($revisions[0]);
        $this->assertEquals('first save', $revinfo['sum']);
        $this->assertEquals(DOKU_CHANGE_TYPE_CREATE, $revinfo['type']);

        sleep(1); // wait for new revision ID

        // save with same content should be ignored
        saveWikiText($page, 'teststring', 'second save', false);
        clearstatcache(false, $file);
        $this->assertEquals($lastmod, filemtime($file));

        $pagelog = new PageChangeLog($page);
        $revisions = $pagelog->getRevisions(-1, 200);
        $this->assertEquals(1, count($revisions));

        // update the page with new text
        saveWikiText($page, 'teststring2', 'third save', false);
        clearstatcache(false, $file);
        $newmod = filemtime($file);
        $this->assertNotEquals($lastmod, $newmod);
        $lastmod = $newmod;

        $pagelog = new PageChangeLog($page);
        $revisions = $pagelog->getRevisions(-1, 200);
        $this->assertEquals(2, count($revisions));
        $revinfo = $pagelog->getRevisionInfo($revisions[0]);
        $this->assertEquals('third save', $revinfo['sum']);
        $this->assertEquals(DOKU_CHANGE_TYPE_EDIT, $revinfo['type']);

        sleep(1); // wait for new revision ID

        // add a minor edit (unauthenticated)
        saveWikiText($page, 'teststring3', 'fourth save', true);
        clearstatcache(false, $file);
        $newmod = filemtime($file);
        $this->assertNotEquals($lastmod, $newmod);
        $lastmod = $newmod;

        $pagelog = new PageChangeLog($page);
        $revisions = $pagelog->getRevisions(-1, 200);
        $this->assertEquals(3, count($revisions));
        $revinfo = $pagelog->getRevisionInfo($revisions[0]);
        $this->assertEquals('fourth save', $revinfo['sum']);
        $this->assertEquals(DOKU_CHANGE_TYPE_EDIT, $revinfo['type']);

        sleep(1); // wait for new revision ID

        // add a minor edit (authenticated)
        $_SERVER['REMOTE_USER'] = 'user';
        saveWikiText($page, 'teststring4', 'fifth save', true);
        clearstatcache(false, $file);
        $newmod = filemtime($file);
        $this->assertNotEquals($lastmod, $newmod);
        $lastmod = $newmod;

        $pagelog = new PageChangeLog($page);
        $revisions = $pagelog->getRevisions(-1, 200);
        $this->assertEquals(4, count($revisions));
        $revinfo = $pagelog->getRevisionInfo($revisions[0]);
        $this->assertEquals('fifth save', $revinfo['sum']);
        $this->assertEquals(DOKU_CHANGE_TYPE_MINOR_EDIT, $revinfo['type']);

        sleep(1); // wait for new revision ID

        // delete
        saveWikiText($page, '', 'sixth save', false);
        clearstatcache(false, $file);
        $this->assertFileNotExists($file);

        $pagelog = new PageChangeLog($page);
        $revisions = $pagelog->getRevisions(-1, 200);
        $this->assertEquals(5, count($revisions));
        $revinfo = $pagelog->getRevisionInfo($revisions[0]);
        $this->assertEquals('sixth save', $revinfo['sum']);
        $this->assertEquals(DOKU_CHANGE_TYPE_DELETE, $revinfo['type']);

        sleep(1); // wait for new revision ID

        // restore
        $REV = $lastmod;
        saveWikiText($page, 'teststring4', 'seventh save', true);
        clearstatcache(false, $file);
        $this->assertFileExists($file);
        $newmod = filemtime($file);
        $this->assertNotEquals($lastmod, $newmod);
        $lastmod = $newmod;

        $pagelog = new PageChangeLog($page);
        $revisions = $pagelog->getRevisions(-1, 200);
        $this->assertEquals(6, count($revisions));
        $revinfo = $pagelog->getRevisionInfo($revisions[0]);
        $this->assertEquals('seventh save', $revinfo['sum']);
        $this->assertEquals(DOKU_CHANGE_TYPE_REVERT, $revinfo['type']);
        $this->assertEquals($REV, $revinfo['extra']);
        $REV = '';

        sleep(1); // wait for new revision ID

        // create external edit
        file_put_contents($file, 'teststring5');

        sleep(1); // wait for new revision ID

        // save on top of external edit
        saveWikiText($page, 'teststring6', 'eigth save', false);
        clearstatcache(false, $file);
        $newmod = filemtime($file);
        $this->assertNotEquals($lastmod, $newmod);
        $lastmod = $newmod;

        $pagelog = new PageChangeLog($page);
        $revisions = $pagelog->getRevisions(-1, 200);
        $this->assertEquals(8, count($revisions)); // two more revisions now!
        $revinfo = $pagelog->getRevisionInfo($revisions[0]);
        $this->assertEquals('eigth save', $revinfo['sum']);
        $this->assertEquals(DOKU_CHANGE_TYPE_EDIT, $revinfo['type']);

        $revinfo = $pagelog->getRevisionInfo($revisions[1]);
        $this->assertEquals('external edit', $revinfo['sum']);
        $this->assertEquals(DOKU_CHANGE_TYPE_EDIT, $revinfo['type']);

    }
}
