<?php

use dokuwiki\ChangeLog\PageChangeLog;

/**
 * saveWikiText() stores files in pages/, attic/ and adds entries to changelog
 */
class common_saveWikiText_test extends DokuWikiTest {
    /** Delay writes of old revisions by a second. */
    public function handle_write(Doku_Event $event, $param) {
        if ($event->data[3] !== false) {
            $this->waitForTick();
        }
    }

    /**
     * Execute a whole bunch of saves on the same page and check the results
     *   Create -> same content edit(ignored) -> edit -> minor edit (unauthenticated)
     *   -> minor edit (authenticated) -> deletion -> revert -> external edit -> edit
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
        $this->assertCount(1, $revisions);
        $this->assertEquals($lastmod, $revisions[0]);
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
        $this->assertCount(1, $revisions);

        // update the page with new text
        saveWikiText($page, 'teststring2long', 'third save', false);
        clearstatcache(false, $file);
        $newmod = filemtime($file);
        $this->assertNotEquals($lastmod, $newmod);
        $lastmod = $newmod;

        $pagelog = new PageChangeLog($page);
        $revisions = $pagelog->getRevisions(-1, 200);
        $this->assertCount(2, $revisions);
        $this->assertEquals($newmod, $revisions[0]);
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
        $this->assertCount(3, $revisions);
        $this->assertEquals($newmod, $revisions[0]);
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
        $this->assertCount(4, $revisions);
        $this->assertEquals($newmod, $revisions[0]);
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
        $this->assertCount(5, $revisions);
        $this->assertNotEquals($lastmod, $revisions[0]);
        $revinfo = $pagelog->getRevisionInfo($revisions[0]);
        $this->assertEquals('sixth save', $revinfo['sum']);
        $this->assertEquals(DOKU_CHANGE_TYPE_DELETE, $revinfo['type']);
        $this->assertEquals(-11, $revinfo['sizechange']);
        $this->assertFileExists(wikiFN($page, $revinfo['date']));

        $files = glob(dirname(wikiFN($page, $revinfo['date'])).'/'.$page.'.*');
        $this->assertCount(5, $files, 'detectExternalEdit() should not add too often old revs');

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
        $this->assertCount(6, $revisions);
        $this->assertEquals($newmod, $revisions[0]);
        $revinfo = $pagelog->getRevisionInfo($revisions[0]);
        $this->assertEquals('seventh save', $revinfo['sum']);
        $this->assertEquals(DOKU_CHANGE_TYPE_REVERT, $revinfo['type']);
        $this->assertEquals($REV, $revinfo['extra']);
        $this->assertEquals(11, $revinfo['sizechange']);
        $this->assertFileExists(wikiFN($page, $revinfo['date']));
        $REV = '';

        $files = glob(dirname(wikiFN($page, $revinfo['date'])).'/'.$page.'.*');
        $this->assertCount(6, $files, 'detectExternalEdit() should not add too often old revs');

        $this->waitForTick(); // wait for new revision ID

        // create external edit
        file_put_contents($file, 'teststring 5');
        clearstatcache(false, $file);
        $externalmod = filemtime($file);

        $pagelog = new PageChangeLog($page);
        $revisions = $pagelog->getRevisions(-1, 200);
        // external edit is not yet in changelog
        $this->assertCount(6, $revisions);
        $revinfo = $pagelog->getRevisionInfo($revisions[0]);
        $this->assertEquals('seventh save', $revinfo['sum'], 'last entry is the revert, not the external deletion');
        $this->assertEquals(DOKU_CHANGE_TYPE_REVERT, $revinfo['type']);
        $this->assertEquals(11, $revinfo['sizechange']);
        $this->assertFileExists(wikiFN($page, $revinfo['date']));
        // external edit info can be requested separatedly
        $extEditRevInfo = $pagelog->getExternalEditRevInfo();
        $this->assertEquals($externalmod, $extEditRevInfo['date']);
        $this->assertEquals('external edit', $extEditRevInfo['sum']);
        $this->assertEquals(DOKU_CHANGE_TYPE_EDIT, $extEditRevInfo['type']);
        $this->assertEquals(1, $extEditRevInfo['sizechange']);
        $this->assertFileNotExists(wikiFN($page, $extEditRevInfo['date']),'page does not yet exist in attic');

        $this->waitForTick(); // wait for new revision ID

        // save on top of external edit
        saveWikiText($page, 'teststring6', 'eigth save', false);
        clearstatcache(false, $file);
        $newmod = filemtime($file);
        $this->assertNotEquals($lastmod, $newmod);

        $pagelog = new PageChangeLog($page);
        $revisions = $pagelog->getRevisions(-1, 200);
        $this->assertCount(8, $revisions); // two more revisions now wrt previous normal save!

        $this->assertEquals($newmod, $revisions[0]);
        $revinfo = $pagelog->getRevisionInfo($revisions[0]);
        $this->assertEquals('eigth save', $revinfo['sum']);
        $this->assertEquals(DOKU_CHANGE_TYPE_EDIT, $revinfo['type']);
        $this->assertEquals(-1, $revinfo['sizechange']);

        $this->assertEquals($externalmod, $revisions[1]);
        $revinfo = $pagelog->getRevisionInfo($revisions[1]);
        $this->assertEquals('external edit', $revinfo['sum']);
        $this->assertEquals(DOKU_CHANGE_TYPE_EDIT, $revinfo['type']);
        $this->assertEquals(1, $revinfo['sizechange']);

    }

    /**
     * Execute a whole bunch of saves on the same page and check the results
     *   Create -> delete -> external create -> edit
     */
    function test_savesequencedeleteexternalrevision() {
        // add an additional delay when saving files to make sure
        // nobody relies on the saving happening in the same second
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
        $this->assertCount(1, $revisions);
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
        $this->assertCount(2, $revisions);
        $revinfo = $pagelog->getRevisionInfo($revisions[0]);
        $this->assertEquals('second save', $revinfo['sum']);
        $this->assertEquals(DOKU_CHANGE_TYPE_DELETE, $revinfo['type']);
        $this->assertEquals(-10, $revinfo['sizechange']);
        $this->assertFileExists(wikiFN($page, $revinfo['date']));

        $this->waitForTick(); // wait for new revision ID

        // create external edit
        file_put_contents($file, 'teststring5');
        clearstatcache(false, $file);
        $lastmod = filemtime($file);

        $pagelog = new PageChangeLog($page);
        $revisions = $pagelog->getRevisions(-1, 200);
        // external edit is not yet in changelog
        $this->assertCount(2, $revisions);
        $revinfo = $pagelog->getRevisionInfo($revisions[0]);
        $this->assertEquals('second save', $revinfo['sum'], 'last entry is the normal deletion, not the external creation');
        $this->assertEquals(DOKU_CHANGE_TYPE_DELETE, $revinfo['type']);
        $this->assertEquals(-10, $revinfo['sizechange']);
        $this->assertFileExists(wikiFN($page, $revinfo['date']));
        // external edit info can be requested separatedly
        $extEditRevInfo = $pagelog->getExternalEditRevInfo();
        $this->assertEquals($lastmod, $extEditRevInfo['date']);
        $this->assertEquals('created - external edit', $extEditRevInfo['sum']);
        $this->assertEquals(DOKU_CHANGE_TYPE_CREATE, $extEditRevInfo['type']); //recreated
        $this->assertEquals(11, $extEditRevInfo['sizechange']);
        $this->assertFileNotExists(wikiFN($page, $extEditRevInfo['date']),'page does not yet exist in attic');

        $this->waitForTick(); // wait for new revision ID

        // save on top of external edit
        saveWikiText($page, 'teststring6', 'third save', false);
        clearstatcache(false, $file);

        $pagelog = new PageChangeLog($page);
        $revisions = $pagelog->getRevisions(-1, 200);
        $this->assertCount(4, $revisions); // two more revisions now!
        $revinfo = $pagelog->getRevisionInfo($revisions[0]);
        $this->assertEquals('third save', $revinfo['sum']);
        $this->assertEquals(DOKU_CHANGE_TYPE_EDIT, $revinfo['type']);
        $this->assertEquals(0, $revinfo['sizechange']);
        $this->assertFileExists(wikiFN($page, $revinfo['date']));

        $revinfo = $pagelog->getRevisionInfo($revisions[1]);
        $this->assertEquals('created - external edit', $revinfo['sum']);
        $this->assertEquals(DOKU_CHANGE_TYPE_CREATE, $revinfo['type']);
        $this->assertEquals(11, $revinfo['sizechange']);
        $this->assertFileExists(wikiFN($page, $revinfo['date']));

    }

    /**
     * Execute a whole bunch of saves on the same page and check the results
     *   External create -> edit
     */
    function test_saveexternalasfirst() {
        $page = 'page3';
        $file = wikiFN($page);

        // create the page
        $this->assertFileNotExists($file);

        // create external edit
        file_put_contents($file, 'teststring');
        clearstatcache(false, $file);
        $lastmod = filemtime($file);

        $pagelog = new PageChangeLog($page);
        $revisions = $pagelog->getRevisions(-1, 200);
        // external edit is not yet in changelog, there exist no changelog
        $this->assertCount(0, $revisions);
        // external edit (run time) info can be requested separatedly
        $extEditRevInfo = $pagelog->getExternalEditRevInfo();
        $this->assertEquals($lastmod, $extEditRevInfo['date']);
        $this->assertEquals('created - external edit', $extEditRevInfo['sum']);
        $this->assertEquals(DOKU_CHANGE_TYPE_CREATE, $extEditRevInfo['type']);
        $this->assertEquals(10, $extEditRevInfo['sizechange']);
        $this->assertFileNotExists(wikiFN($page, $extEditRevInfo['date']),'page does not yet exist in attic');

        $this->waitForTick(true); // wait for new revision ID

        // save on top of external edit
        saveWikiText($page, 'teststring6', 'first save', false);
        clearstatcache(false, $file);

        $pagelog = new PageChangeLog($page);
        $revisions = $pagelog->getRevisions(-1, 200);
        $this->assertCount(2, $revisions); // two more revisions now!
        $revinfo = $pagelog->getRevisionInfo($revisions[0]);
        $this->assertEquals('first save', $revinfo['sum']);
        $this->assertEquals(DOKU_CHANGE_TYPE_EDIT, $revinfo['type']);
        $this->assertEquals(1, $revinfo['sizechange']);

        $revinfo = $pagelog->getRevisionInfo($revisions[1]);
        $this->assertEquals('created - external edit', $revinfo['sum']);
        $this->assertEquals(DOKU_CHANGE_TYPE_CREATE, $revinfo['type']);
        $this->assertEquals(10, $revinfo['sizechange']);

    }

    /**
     * Execute a whole bunch of saves on the same page and check the results
     *   Create -> external delete -> create
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
        $this->assertCount(1, $revisions);
        $revinfo = $pagelog->getRevisionInfo($revisions[0]);
        $this->assertEquals($lastmod, $revinfo['date']);
        $this->assertEquals('first save', $revinfo['sum']);
        $this->assertEquals(DOKU_CHANGE_TYPE_CREATE, $revinfo['type']);
        $this->assertEquals(10, $revinfo['sizechange']);

        $files = glob(dirname(wikiFN($page, $revinfo['date'])).'/'.$page.'.*');
        $this->assertCount(1, $files, 'detectExternalEdit() should not add too often old revs');

        $this->waitForTick(true); // wait for new revision ID


        // create external delete
        unlink($file);
        clearstatcache(false, $file);
        $lastmod = 9999999999; //deletion date unknown, to remainders with a date

        $pagelog = new PageChangeLog($page);
        $revisions = $pagelog->getRevisions(-1, 200);
        // external edit is not yet in changelog
        $this->assertCount(1, $revisions);
        $revinfo = $pagelog->getRevisionInfo($revisions[0]);
        $this->assertEquals('first save', $revinfo['sum'], 'last entry is the normal create, not the external deletion');
        $this->assertEquals(DOKU_CHANGE_TYPE_CREATE, $revinfo['type']);
        $this->assertEquals(10, $revinfo['sizechange']);
        $this->assertFileExists(wikiFN($page, $revinfo['date']));
        // external edit info can be requested separatedly
        $extEditRevInfo = $pagelog->getExternalEditRevInfo();
        $this->assertEquals($lastmod, $extEditRevInfo['date']);
        $this->assertEquals('removed - external edit', $extEditRevInfo['sum']);
        $this->assertEquals(DOKU_CHANGE_TYPE_DELETE, $extEditRevInfo['type']);
        $this->assertEquals(-10, $extEditRevInfo['sizechange']);
        $this->assertFileNotExists(wikiFN($page, $extEditRevInfo['date']),'page does not yet exist in attic');

        $this->waitForTick(); // wait for new revision ID

        // save on top of external delete. save is seen as creation
        saveWikiText($page, 'teststring6', 'second save', false);
        clearstatcache(false, $file);
        $lastmod = filemtime($file);


        $pagelog = new PageChangeLog($page);
        $revisions = $pagelog->getRevisions(-1, 200);
        $this->assertCount(3, $revisions); // two more revisions
        $revinfo = $pagelog->getRevisionInfo($revisions[0]);
        $this->assertEquals($lastmod, $revinfo['date']);
        $this->assertEquals('second save', $revinfo['sum']);
        $this->assertEquals(DOKU_CHANGE_TYPE_CREATE, $revinfo['type']);
        $this->assertEquals(11, $revinfo['sizechange']);

        $revinfo = $pagelog->getRevisionInfo($revisions[1]);
        $this->assertTrue($revinfo['date'] < $lastmod); //date of external deletion is 'current time at saveWikiText call'-1
        $this->assertEquals('removed - external edit', $revinfo['sum']);
        $this->assertEquals(DOKU_CHANGE_TYPE_DELETE, $revinfo['type']);
        $this->assertEquals(-10, $revinfo['sizechange']);
        $this->assertFileExists(wikiFN($page, $revinfo['date']),'now added to attic as well');

        $files = glob(dirname(wikiFN($page, $revinfo['date'])).'/'.$page.'.*');
        $this->assertCount(3, $files, 'detectExternalEdit() should not add too often old revs');

    }


    /**
     * Execute a whole bunch of saves on the same page and check the results
     *   Create -> same content edit(ignored) -> edit -> minor edit (unauthenticated)
     *   -> minor edit (authenticated) -> revert
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
        $this->assertCount(1, $revisions);
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
        $this->assertCount(1, $revisions);

        // update the page with new text
        saveWikiText($page, 'teststring2long', 'third save', false);
        clearstatcache(false, $file);
        $newmod = filemtime($file);
        $this->assertNotEquals($lastmod, $newmod);
        $lastmod = $newmod;
        $revertrev = $newmod;

        $pagelog = new PageChangeLog($page);
        $revisions = $pagelog->getRevisions(-1, 200);
        $this->assertCount(2, $revisions);
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
        $this->assertCount(3, $revisions);
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
        $this->assertCount(4, $revisions);
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
        $this->assertCount(5, $revisions);
        $revinfo = $pagelog->getRevisionInfo($revisions[0]);
        $this->assertEquals('sixth save', $revinfo['sum']);
        $this->assertEquals(DOKU_CHANGE_TYPE_REVERT, $revinfo['type']);
        $this->assertEquals($REV, $revinfo['extra']);
        $this->assertEquals(4, $revinfo['sizechange']);
        $REV = '';
    }

    /**
     * Execute a whole bunch of saves on the same page and check the results
     *   Create -> external edit -> edit
     */
    function test_savesequenceexternaledit() {
        $page = 'page6';
        $file = wikiFN($page);

        // create the page
        $this->assertFileNotExists($file);
        saveWikiText($page, 'teststring', 'first save', false);
        $this->assertFileExists($file);

        $pagelog = new PageChangeLog($page);
        $revisions = $pagelog->getRevisions(-1, 200);
        $this->assertCount(1, $revisions);
        $revinfo = $pagelog->getRevisionInfo($revisions[0]);
        $this->assertEquals('first save', $revinfo['sum']);
        $this->assertEquals(DOKU_CHANGE_TYPE_CREATE, $revinfo['type']);
        $this->assertEquals(10, $revinfo['sizechange']);

        $this->waitForTick(true); // wait for new revision ID

        // create external edit
        file_put_contents($file, 'teststring2');
        clearstatcache(false, $file);
        $lastmod = filemtime($file);

        $pagelog = new PageChangeLog($page);
        $revisions = $pagelog->getRevisions(-1, 200);
        // external edit is not yet in changelog
        $this->assertCount(1, $revisions);
        $revinfo = $pagelog->getRevisionInfo($revisions[0]);
        $this->assertEquals('first save', $revinfo['sum'], 'last entry is the normal create, not the external edit');
        $this->assertEquals(DOKU_CHANGE_TYPE_CREATE, $revinfo['type']);
        $this->assertEquals(10, $revinfo['sizechange']);
        $this->assertFileExists(wikiFN($page, $revinfo['date']));
        // external edit info should be requested separatedly
        $extEditRevInfo = $pagelog->getExternalEditRevInfo();
        $this->assertEquals($lastmod, $extEditRevInfo['date']);
        $this->assertEquals('external edit', $extEditRevInfo['sum']);
        $this->assertEquals(DOKU_CHANGE_TYPE_EDIT, $extEditRevInfo['type']);
        $this->assertEquals(1, $extEditRevInfo['sizechange']);
        $this->assertFileNotExists(wikiFN($page, $extEditRevInfo['date']),'page does not yet exist in attic');

        $files = glob(dirname(wikiFN($page, $revinfo['date'])).'/'.$page.'.*');
        $this->assertCount(1, $files, 'detectExternalEdit() should not add too often old revs');

        $this->waitForTick(); // wait for new revision ID

        // save on top of external edit.
        saveWikiText($page, 'teststring three', 'third save', false);
        clearstatcache(false, $file);

        $pagelog = new PageChangeLog($page);
        $revisions = $pagelog->getRevisions(-1, 200);
        $this->assertCount(3, $revisions); // one more revisions now!
        $revinfo = $pagelog->getRevisionInfo($revisions[0]);
        $this->assertEquals('third save', $revinfo['sum']);
        $this->assertEquals(DOKU_CHANGE_TYPE_EDIT, $revinfo['type']);
        $this->assertEquals(5, $revinfo['sizechange']);

        $revinfo = $pagelog->getRevisionInfo($revisions[1]);
        $this->assertEquals('external edit', $revinfo['sum']);
        $this->assertEquals(DOKU_CHANGE_TYPE_EDIT, $revinfo['type']);
        $this->assertEquals(1, $revinfo['sizechange']);
        $this->assertFileExists(wikiFN($page, $revinfo['date']),'now added to attic as well');

        $files = glob(dirname(wikiFN($page, $revinfo['date'])).'/'.$page.'.*');
        $this->assertCount(3, $files, 'detectExternalEdit() should not add too often old revs');


        $this->waitForTick(); // wait for new revision ID

        // create external edit
        file_put_contents($file, 'teststring 4');
        clearstatcache(false, $file);
        global $cache_externaledit;
        unset($cache_externaledit[$page]); //assumption is that not multiple external edits happen in one run
        $lastmodext = filemtime($file);

        $pagelog = new PageChangeLog($page);
        $revisions = $pagelog->getRevisions(-1, 200);
        // external edit is not yet in changelog
        $this->assertCount(3, $revisions);
        $revinfo = $pagelog->getRevisionInfo($revisions[0]);
        $this->assertEquals('third save', $revinfo['sum'], 'last entry is the normal edit, not the external edit');
        $this->assertEquals(DOKU_CHANGE_TYPE_EDIT, $revinfo['type']);
        $this->assertEquals(5, $revinfo['sizechange']);
        $this->assertFileExists(wikiFN($page, $revinfo['date']));
        // external edit info should be requested separatedly
        $extEditRevInfo = $pagelog->getExternalEditRevInfo();
        $this->assertEquals($lastmodext, $extEditRevInfo['date']);
        $this->assertEquals('external edit', $extEditRevInfo['sum']);
        $this->assertEquals(DOKU_CHANGE_TYPE_EDIT, $extEditRevInfo['type']);
        $this->assertEquals(-4, $extEditRevInfo['sizechange']);
        $this->assertFileNotExists(wikiFN($page, $extEditRevInfo['date']),'page does not yet exist in attic');

        $this->waitForTick(); // wait for new revision ID

        // save on top of external edit.
        saveWikiText($page, 'teststring five', 'fifth save', false);
        clearstatcache(false, $file);
        $lastmod = filemtime($file);

        $pagelog = new PageChangeLog($page);
        $revisions = $pagelog->getRevisions(-1, 200);
        $this->assertCount(5, $revisions); // one more revisions now!
        $revinfo = $pagelog->getRevisionInfo($revisions[0]);
        $this->assertEquals($lastmod, $revinfo['date']);
        $this->assertEquals('fifth save', $revinfo['sum']);
        $this->assertEquals(DOKU_CHANGE_TYPE_EDIT, $revinfo['type']);
        $this->assertEquals(3, $revinfo['sizechange']);

        $revinfo = $pagelog->getRevisionInfo($revisions[1]);
        $this->assertEquals($lastmodext, $revinfo['date']);
        $this->assertEquals('external edit', $revinfo['sum']);
        $this->assertEquals(DOKU_CHANGE_TYPE_EDIT, $revinfo['type']);
        $this->assertEquals(-4, $revinfo['sizechange']);
        $this->assertFileExists(wikiFN($page, $revinfo['date']),'now added to attic as well');

        $files = glob(dirname(wikiFN($page, $revinfo['date'])).'/'.$page.'.*');
        $this->assertCount(5, $files, 'detectExternalEdit() should not add too often old revs');

        $this->waitForTick(); // wait for new revision ID


        // create external delete
        unlink($file);
        clearstatcache(false, $file);
        unset($cache_externaledit[$page]); //assumption is that not multiple external edits happen in one run
        $lastmod = 9999999999; //deletion date unknown, to remainders with a date

        $pagelog = new PageChangeLog($page);
        $revisions = $pagelog->getRevisions(-1, 200);
        // external edit is not yet in changelog
        $this->assertCount(5, $revisions);
        $revinfo = $pagelog->getRevisionInfo($revisions[0]);
        $this->assertEquals('fifth save', $revinfo['sum'], 'last entry is the normal edit, not the external deletion');
        $this->assertEquals(DOKU_CHANGE_TYPE_EDIT, $revinfo['type']);
        $this->assertEquals(3, $revinfo['sizechange']);
        $this->assertFileExists(wikiFN($page, $revinfo['date']));
        // external edit info can be requested separatedly
        $extEditRevInfo = $pagelog->getExternalEditRevInfo();
        $this->assertEquals($lastmod, $extEditRevInfo['date']);
        $this->assertEquals('removed - external edit', $extEditRevInfo['sum']);
        $this->assertEquals(DOKU_CHANGE_TYPE_DELETE, $extEditRevInfo['type']);
        $this->assertEquals(-15, $extEditRevInfo['sizechange']);
        $this->assertFileNotExists(wikiFN($page, $extEditRevInfo['date']),'9999999999 should not exist in attic');


        $this->waitForTick(); // wait for new revision ID

        // save on top of external delete. save is seen as creation
        saveWikiText($page, 'teststring7', 'seventh save', false);
        clearstatcache(false, $file);
        $lastmod = filemtime($file);

        $pagelog = new PageChangeLog($page);
        $revisions = $pagelog->getRevisions(-1, 200);
        $this->assertCount(7, $revisions); // two more revisions
        $revinfo = $pagelog->getRevisionInfo($revisions[0]);
        $this->assertEquals($lastmod, $revinfo['date']);
        $this->assertEquals('seventh save', $revinfo['sum']);
        $this->assertEquals(DOKU_CHANGE_TYPE_CREATE, $revinfo['type']);
        $this->assertEquals(11, $revinfo['sizechange']);

        $revinfo = $pagelog->getRevisionInfo($revisions[1]);
        $this->assertTrue($revinfo['date'] < $lastmod); //date of external deletion is 'current time at saveWikiText call'-1
        $this->assertEquals('removed - external edit', $revinfo['sum']); //TODO: removed - external edit
        $this->assertEquals(DOKU_CHANGE_TYPE_DELETE, $revinfo['type']); //TODO: DOKU_CHANGE_TYPE_DELETE
        $this->assertEquals(-15, $revinfo['sizechange']); //TODO: -15
        $this->assertFileExists(wikiFN($page, $revinfo['date']),'now added to attic as well');

        $files = glob(dirname(wikiFN($page, $revinfo['date'])).'/'.$page.'.*');
        $this->assertCount(7, $files, 'detectExternalEdit() should not add too often old revs');
    }

}
