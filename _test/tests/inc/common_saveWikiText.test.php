<?php

use dokuwiki\ChangeLog\PageChangeLog;

class common_saveWikiText_test extends DokuWikiTest {
    /** Delay writes of old revisions by a second. */
    public function handle_write(Doku_Event $event, $param) {
        if ($event->data[3] !== false) {
            $this->waitForTick();
        }
    }

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
        $this->assertEquals(10, $revinfo['sizechange']);
        $this->assertFileExists(wikiFN($page, $revinfo['date']));

        $this->waitForTick(true); // wait for new revision ID

        // save with same content should be ignored
        saveWikiText($page, 'teststring', 'second save', false);
        clearstatcache(false, $file);
        $this->assertEquals($lastmod, filemtime($file));

        $pagelog = new PageChangeLog($page);
        $revisions = $pagelog->getRevisions(-1, 200);
        $this->assertEquals(1, count($revisions));

        // update the page with new text
        saveWikiText($page, 'teststring2long', 'third save', false);
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
        $this->assertEquals(5, $revinfo['sizechange']);

        $this->waitForTick(); // wait for new revision ID

        // add a minor edit (unauthenticated)
        saveWikiText($page, 'teststring3long', 'fourth save', true);
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
        $this->assertEquals(0, $revinfo['sizechange']);

        $this->waitForTick(); // wait for new revision ID

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
        $this->assertEquals(-4, $revinfo['sizechange']);

        $this->waitForTick(); // wait for new revision ID

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
        $this->assertEquals(-11, $revinfo['sizechange']);
        $this->assertFileExists(wikiFN($page, $revinfo['date']));

        $this->waitForTick(); // wait for new revision ID

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
        $this->assertEquals(11, $revinfo['sizechange']);
        $this->assertFileExists(wikiFN($page, $revinfo['date']));
        $REV = '';

        $this->waitForTick(); // wait for new revision ID

        // create external edit
        file_put_contents($file, 'teststring5');

        $this->waitForTick(); // wait for new revision ID

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
        $this->assertEquals(0, $revinfo['sizechange']);

        $revinfo = $pagelog->getRevisionInfo($revisions[1]);
        $this->assertEquals('external edit', $revinfo['sum']);
        $this->assertEquals(DOKU_CHANGE_TYPE_EDIT, $revinfo['type']);
        $this->assertEquals(0, $revinfo['sizechange']);

    }

    /**
     * Execute a whole bunch of saves on the same page and check the results
     */
    function test_savesequencedeleteexternalrevision() {
        // add an additional delay when saving files to make sure
        // nobody relies on the saving happening in the same second
        /** @var $EVENT_HANDLER \dokuwiki\Extension\EventHandler */
        global $EVENT_HANDLER;
        $EVENT_HANDLER->register_hook('IO_WIKIPAGE_WRITE', 'BEFORE', $this, 'handle_write');

        $page = 'page2';
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
        $this->assertEquals(10, $revinfo['sizechange']);
        $this->assertFileExists(wikiFN($page, $revinfo['date']));

        $this->waitForTick(true); // wait for new revision ID

        // delete
        saveWikiText($page, '', 'second save', false);
        clearstatcache(false, $file);
        $this->assertFileNotExists($file);

        $pagelog = new PageChangeLog($page);
        $revisions = $pagelog->getRevisions(-1, 200);
        $this->assertEquals(2, count($revisions));
        $revinfo = $pagelog->getRevisionInfo($revisions[0]);
        $this->assertEquals('second save', $revinfo['sum']);
        $this->assertEquals(DOKU_CHANGE_TYPE_DELETE, $revinfo['type']);
        $this->assertEquals(-10, $revinfo['sizechange']);
        $this->assertFileExists(wikiFN($page, $revinfo['date']));

        $this->waitForTick(); // wait for new revision ID

        // create external edit
        file_put_contents($file, 'teststring5');

        $this->waitForTick(); // wait for new revision ID

        // save on top of external edit
        saveWikiText($page, 'teststring6', 'third save', false);
        clearstatcache(false, $file);

        $pagelog = new PageChangeLog($page);
        $revisions = $pagelog->getRevisions(-1, 200);
        $this->assertEquals(4, count($revisions)); // two more revisions now!
        $revinfo = $pagelog->getRevisionInfo($revisions[0]);
        $this->assertEquals('third save', $revinfo['sum']);
        $this->assertEquals(DOKU_CHANGE_TYPE_EDIT, $revinfo['type']);
        $this->assertEquals(0, $revinfo['sizechange']);
        $this->assertFileExists(wikiFN($page, $revinfo['date']));

        $revinfo = $pagelog->getRevisionInfo($revisions[1]);
        $this->assertEquals('external edit', $revinfo['sum']);
        $this->assertEquals(DOKU_CHANGE_TYPE_EDIT, $revinfo['type']);
        $this->assertEquals(11, $revinfo['sizechange']);
        $this->assertFileExists(wikiFN($page, $revinfo['date']));

    }

    /**
     * Execute a whole bunch of saves on the same page and check the results
     */
    function test_saveexternalasfirst() {
        $page = 'page3';
        $file = wikiFN($page);

        // create the page
        $this->assertFileNotExists($file);

        // create external edit
        file_put_contents($file, 'teststring');

        $this->waitForTick(true); // wait for new revision ID

        // save on top of external edit
        saveWikiText($page, 'teststring6', 'first save', false);
        clearstatcache(false, $file);

        $pagelog = new PageChangeLog($page);
        $revisions = $pagelog->getRevisions(-1, 200);
        $this->assertEquals(2, count($revisions)); // two more revisions now!
        $revinfo = $pagelog->getRevisionInfo($revisions[0]);
        $this->assertEquals('first save', $revinfo['sum']);
        $this->assertEquals(DOKU_CHANGE_TYPE_EDIT, $revinfo['type']);
        $this->assertEquals(1, $revinfo['sizechange']);

        $revinfo = $pagelog->getRevisionInfo($revisions[1]);
        $this->assertEquals('external edit', $revinfo['sum']);
        $this->assertEquals(DOKU_CHANGE_TYPE_EDIT, $revinfo['type']);
        $this->assertEquals(10, $revinfo['sizechange']);

    }

    /**
     * Execute a whole bunch of saves on the same page and check the results
     */
    function test_savesequenceexternaldeleteedit() {
        $page = 'page4';
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
        $this->assertEquals(10, $revinfo['sizechange']);

        $this->waitForTick(true); // wait for new revision ID


        // create external delete
        unlink($file);
        clearstatcache(false, $file);

        $this->waitForTick(); // wait for new revision ID

        // save on top of external delete. save is seen as creation
        saveWikiText($page, 'teststring6', 'second save', false);
        clearstatcache(false, $file);

        $pagelog = new PageChangeLog($page);
        $revisions = $pagelog->getRevisions(-1, 200);
        $this->assertEquals(2, count($revisions)); // one more revisions now!
        $revinfo = $pagelog->getRevisionInfo($revisions[0]);
        $this->assertEquals('second save', $revinfo['sum']);
        $this->assertEquals(DOKU_CHANGE_TYPE_CREATE, $revinfo['type']);
        $this->assertEquals(11, $revinfo['sizechange']);

        $revinfo = $pagelog->getRevisionInfo($revisions[1]);
        $this->assertEquals('first save', $revinfo['sum']);

    }

    /**
     * Execute a whole bunch of saves on the same page and check the results
     */
    function test_savesequencerevert() {
        global $REV;

        $page = 'page5';
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
        $this->assertEquals(10, $revinfo['sizechange']);

        $this->waitForTick(true); // wait for new revision ID

        // save with same content should be ignored
        saveWikiText($page, 'teststring', 'second save', false);
        clearstatcache(false, $file);
        $this->assertEquals($lastmod, filemtime($file));

        $pagelog = new PageChangeLog($page);
        $revisions = $pagelog->getRevisions(-1, 200);
        $this->assertEquals(1, count($revisions));

        // update the page with new text
        saveWikiText($page, 'teststring2long', 'third save', false);
        clearstatcache(false, $file);
        $newmod = filemtime($file);
        $this->assertNotEquals($lastmod, $newmod);
        $lastmod = $newmod;
        $revertrev = $newmod;

        $pagelog = new PageChangeLog($page);
        $revisions = $pagelog->getRevisions(-1, 200);
        $this->assertEquals(2, count($revisions));
        $revinfo = $pagelog->getRevisionInfo($revisions[0]);
        $this->assertEquals('third save', $revinfo['sum']);
        $this->assertEquals(DOKU_CHANGE_TYPE_EDIT, $revinfo['type']);
        $this->assertEquals(5, $revinfo['sizechange']);

        $this->waitForTick(); // wait for new revision ID

        // add a minor edit (unauthenticated)
        saveWikiText($page, 'teststring3long', 'fourth save', true);
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
        $this->assertEquals(0, $revinfo['sizechange']);

        $this->waitForTick(); // wait for new revision ID

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
        $this->assertEquals(-4, $revinfo['sizechange']);

        $this->waitForTick(); // wait for new revision ID

        // restore
        $REV = $revertrev;
        saveWikiText($page, 'teststring2long', 'sixth save', true);
        clearstatcache(false, $file);
        $this->assertFileExists($file);
        $newmod = filemtime($file);
        $this->assertNotEquals($lastmod, $newmod);

        $pagelog = new PageChangeLog($page);
        $revisions = $pagelog->getRevisions(-1, 200);
        $this->assertEquals(5, count($revisions));
        $revinfo = $pagelog->getRevisionInfo($revisions[0]);
        $this->assertEquals('sixth save', $revinfo['sum']);
        $this->assertEquals(DOKU_CHANGE_TYPE_REVERT, $revinfo['type']);
        $this->assertEquals($REV, $revinfo['extra']);
        $this->assertEquals(4, $revinfo['sizechange']);
        $REV = '';
    }

}
