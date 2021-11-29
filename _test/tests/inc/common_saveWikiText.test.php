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
     * assertions against changelog entries and attic after saveWikiText()
     */
    private function checkChangeLogAfterNormalSave(
        PageChangeLog $pagelog,
        $expectedRevs,               // @param int
        &$expectedLastEntry,         // @param array, pass by reference
        $expected2ndLastEntry = null // @param array (optional)
    ) {
        $revisions = $pagelog->getRevisions(-1, 200);
        $this->assertCount($expectedRevs, $revisions);
        $this->assertCount($expectedRevs, array_unique($revisions), 'date duplicated in changelog');
        // last revision
        $lastRevInfo = $pagelog->getRevisionInfo($revisions[0]);
        $expectedLastEntry += $lastRevInfo;
        $this->assertEquals($expectedLastEntry, $lastRevInfo);
        // current revision
        $currentRevInfo = $pagelog->getCurrentRevisionInfo();
        $this->assertEquals($currentRevInfo, $lastRevInfo, 'current & last revs should be identical');
        // attic
        $attic = wikiFN($lastRevInfo['id'], $lastRevInfo['date']);
        $this->assertFileExists($attic, 'file missing in attic');
        $files = count(glob(dirname($attic).'/'.noNS($lastRevInfo['id']).'.*'));
        $this->assertLessThanOrEqual($expectedRevs, $files, 'detectExternalEdit() should not add too often old revs');

        // second to last revision (optional), intended to check logline of previous external edits
        if ($expected2ndLastEntry && count($revisions) > 1) {
            $prevRevInfo = $pagelog->getRevisionInfo($revisions[1]);
            unset($expected2ndLastEntry['timestamp']); // drop timestamp key
            $this->assertEquals($expected2ndLastEntry, $prevRevInfo);
        }
    }

    /**
     * assertions against changelog entries and attic after external edit, create or deletion of page
     */
    private function checkChangeLogAfterExternalEdit(
        PageChangeLog $pagelog,
        $expectedRevs,          // @param int
        $expectedLastEntry,     // @param array
        &$expectedCurrentEntry  // @param array, pass by reference
    ) {
        $revisions = $pagelog->getRevisions(-1, 200);
        $this->assertCount($expectedRevs, $revisions);
        $this->assertCount($expectedRevs, array_unique($revisions), 'date duplicated in changelog');
        // last revision
        if ($expectedRevs > 0) {
            $lastRevInfo = $pagelog->getRevisionInfo($revisions[0]);
            $expectedLastEntry += $lastRevInfo;
            $this->assertEquals($expectedLastEntry, $lastRevInfo);
        } else {
            $this->assertFalse($pagelog->lastRevision(), 'changelog file does not yet exist');
        }
        // current revision
        $currentRevInfo = $pagelog->getCurrentRevisionInfo();
        $this->assertArrayHasKey('timestamp', $currentRevInfo, 'should be external revision');
        $expectedCurrentEntry += $currentRevInfo;
        if ($expectedRevs > 0) {
            $this->assertEquals($expectedCurrentEntry, $currentRevInfo);
                                
        }
        // attic
        $attic = wikiFN($currentRevInfo['id'], $currentRevInfo['date']);
        $this->assertFileNotExists($attic, 'page does not yet exist in attic');
    }


    /**
     * Execute a whole bunch of saves on the same page and check the results
     * TEST 1
     *  1.1 create a page
     *  1.2 save with same content should be ignored
     *  1.3 update the page with new text
     *  1.4 add a minor edit (unauthenticated, minor not allowable)
     *  1.5 add a minor edit (authenticated)
     *  1.6 delete
     *  1.7 restore
     *  1.8 external edit
     *  1.9 edit and save on top of external edit
     */
    function test_savesequence1() {
        global $REV;

        $page = 'page';
        $file = wikiFN($page);
        $this->assertFileNotExists($file);

        // 1.1 create a page
        saveWikiText($page, 'teststring', '1st save', false);
        clearstatcache(false, $file);
        $this->assertFileExists($file);
        $lastmod = filemtime($file);
        $expectedRevs = 1;
        $expect = array(
            'date' => $lastmod,
            'type' => DOKU_CHANGE_TYPE_CREATE,
            'sum'  => '1st save',
            'sizechange' => 10, // = strlen('teststring')
        );

        $pagelog = new PageChangeLog($page);
        $this->checkChangeLogAfterNormalSave($pagelog, $expectedRevs, $expect);

        $this->waitForTick(true); // wait for new revision ID

        // 1.2 save with same content should be ignored
        saveWikiText($page, 'teststring', '2nd save', false);
        clearstatcache(false, $file);
        $this->assertEquals($lastmod, filemtime($file));

        $pagelog = new PageChangeLog($page);
        $revisions = $pagelog->getRevisions(-1, 200);
        $this->assertCount(1, $revisions);

        // 1.3 update the page with new text
        saveWikiText($page, 'teststring2long', '3rd save', false);
        clearstatcache(false, $file);
        $newmod = filemtime($file);
        $this->assertNotEquals($lastmod, $newmod);
        $lastmod = $newmod;
        $expectedRevs = 2;
        $expectPrev = $expect;
        $expect = array(
            'date' => $lastmod,
            'type' => DOKU_CHANGE_TYPE_EDIT,
            'sum'  => '3rd save',
            'sizechange' => 5,
        );

        $pagelog = new PageChangeLog($page);
        $this->checkChangeLogAfterNormalSave($pagelog, $expectedRevs, $expect, $expectPrev);

        $this->waitForTick(); // wait for new revision ID

        // 1.4 add a minor edit (unauthenticated, minor not allowable)
        saveWikiText($page, 'teststring3long', '4th save', true);
        clearstatcache(false, $file);
        $newmod = filemtime($file);
        $this->assertNotEquals($lastmod, $newmod);
        $lastmod = $newmod;
        $expectedRevs = 3;
        $expect = array(
            'date' => $lastmod,
            'type' => DOKU_CHANGE_TYPE_EDIT,
            'sum'  => '4th save',
            'sizechange' => 0,
        );

        $pagelog = new PageChangeLog($page);
        $this->checkChangeLogAfterNormalSave($pagelog, $expectedRevs, $expect);

        $this->waitForTick(); // wait for new revision ID

        // 1.5 add a minor edit (authenticated)
        $_SERVER['REMOTE_USER'] = 'user';
        saveWikiText($page, 'teststring4', '5th save', true);
        clearstatcache(false, $file);
        $newmod = filemtime($file);
        $this->assertNotEquals($lastmod, $newmod);
        $lastmod = $newmod;
        $expectedRevs = 4;
        $expect = array(
            'date' => $lastmod,
            'type' => DOKU_CHANGE_TYPE_MINOR_EDIT,
            'sum'  => '5th save',
            'sizechange' => -4,
        );

        $pagelog = new PageChangeLog($page);
        $this->checkChangeLogAfterNormalSave($pagelog, $expectedRevs, $expect);

        $this->waitForTick(); // wait for new revision ID

        // 1.6 delete
        saveWikiText($page, '', '6th save', false);
        clearstatcache(false, $file);
        $this->assertFileNotExists($file);
        $expectedRevs = 5;
        $expect = array(
          //'date' => $lastmod, // ignore from lastRev assertion, but confirm attic file existence
            'type' => DOKU_CHANGE_TYPE_DELETE,
            'sum'  => '6th save',
            'sizechange' => -11,
        );

        $pagelog = new PageChangeLog($page);
        $this->checkChangeLogAfterNormalSave($pagelog, $expectedRevs, $expect);

        $this->waitForTick(); // wait for new revision ID

        // 1.7 restore
        $REV = $lastmod;
        saveWikiText($page, 'teststring4', '7th save', true);
        clearstatcache(false, $file);
        $this->assertFileExists($file);
        $newmod = filemtime($file);
        $this->assertNotEquals($lastmod, $newmod);
        $lastmod = $newmod;
        $expectedRevs = 6;
        $expect = array(
            'date' => $lastmod,
            'type' => DOKU_CHANGE_TYPE_REVERT,
            'sum'  => '7th save',
            'sizechange' => 11,
        );

        $pagelog = new PageChangeLog($page);
        $this->checkChangeLogAfterNormalSave($pagelog, $expectedRevs, $expect);
        $REV = '';

        $this->waitForTick(); // wait for new revision ID

        // 1.8 external edit
        file_put_contents($file, 'teststring5 external edit');
        clearstatcache(false, $file);
        $newmod = filemtime($file);
        $this->assertNotEquals($lastmod, $newmod);
        $lastmod = $newmod;
        $expectedRevs = 6; // external edit is not yet in changelog
        $expectExternal = array(
            'date' => $lastmod,
            'type' => DOKU_CHANGE_TYPE_EDIT,
            'sum'  => 'external edit',
            'sizechange' => 14,
        );

        $pagelog = new PageChangeLog($page);
        $this->checkChangeLogAfterExternalEdit($pagelog, $expectedRevs, $expect, $expectExternal);

        $this->waitForTick(); // wait for new revision ID

        // 1.9 save on top of external edit
        saveWikiText($page, 'teststring6', '8th save', false);
        clearstatcache(false, $file);
        $newmod = filemtime($file);
        $this->assertNotEquals($lastmod, $newmod);
        $lastmod = $newmod;
        $expectedRevs = 8;
        $expect = array(
            'date' => $lastmod,
            'type' => DOKU_CHANGE_TYPE_EDIT,
            'sum'  => '8th save',
            'sizechange' => -14,
        );

        $pagelog = new PageChangeLog($page);
        $this->checkChangeLogAfterNormalSave($pagelog, $expectedRevs, $expect, $expectExternal);
    }

    /**
     * Execute a whole bunch of saves on the same page and check the results
     * using $this->handle_write() in event IO_WIKIPAGE_WRITE
     * TEST 2 - create a page externally in 2.3, while external edit in Test 1.8
     *  2.1 create a page
     *  2.2 delete
     *  2.3 externally create the page
     *  2.4 edit and save on top of external edit
     *  2.5 external edit
     *  2.6 edit and save on top of external edit, again
     */
    function test_savesequence2() {
        // add an additional delay when saving files to make sure
        // nobody relies on the saving happening in the same second
        /** @var $EVENT_HANDLER \dokuwiki\Extension\EventHandler */
        global $EVENT_HANDLER;
        $EVENT_HANDLER->register_hook('IO_WIKIPAGE_WRITE', 'BEFORE', $this, 'handle_write');

        $page = 'page2';
        $file = wikiFN($page);
        $this->assertFileNotExists($file);

        // 2.1 create a page
        saveWikiText($page, 'teststring', 'Test 2, 1st save', false);
        clearstatcache(false, $file);
        $this->assertFileExists($file);
        $lastmod = filemtime($file);
        $expectedRevs = 1;
        $expect = array(
            'date' => $lastmod,
            'type' => DOKU_CHANGE_TYPE_CREATE,
            'sum'  => 'Test 2, 1st save',
            'sizechange' => 10, // = strlen('teststring')
        );

        $pagelog = new PageChangeLog($page);
        $this->checkChangeLogAfterNormalSave($pagelog, $expectedRevs, $expect);

        $this->waitForTick(true); // wait for new revision ID

        // 2.2 delete
        saveWikiText($page, '', 'Test 2, 2nd save', false);
        clearstatcache(false, $file);
        $this->assertFileNotExists($file);
        $expectedRevs = 2;
        $expect = array(
          //'date' => $lastmod, // ignore from lastRev assertion, but confirm attic file existence
            'type' => DOKU_CHANGE_TYPE_DELETE,
            'sum'  => 'Test 2, 2nd save',
            'sizechange' => -10,
        );

        $pagelog = new PageChangeLog($page);
        $this->checkChangeLogAfterNormalSave($pagelog, $expectedRevs, $expect);

        $this->waitForTick(); // wait for new revision ID

        // 2.3 externally create the page
        file_put_contents($file, 'teststring5');
        clearstatcache(false, $file);
        $lastmod = filemtime($file);
        $expectedRevs = 2; // external edit is not yet in changelog
        $expectExternal = array(
            'date' => $lastmod,
            'type' => DOKU_CHANGE_TYPE_CREATE,
            'sum'  => 'created - external edit',
            'sizechange' => 11,
        );

        $pagelog = new PageChangeLog($page);
        $this->checkChangeLogAfterExternalEdit($pagelog, $expectedRevs, $expect, $expectExternal);

        $this->waitForTick(); // wait for new revision ID

        // 2.4 save on top of external edit
        saveWikiText($page, 'teststring6', 'Test 2, 3rd save', false);
        clearstatcache(false, $file);
        $newmod = filemtime($file);
        $this->assertNotEquals($lastmod, $newmod);
        $lastmod = $newmod;
        $expectedRevs = 4; // two more revisions now!
        $expect = array(
            'date' => $lastmod,
            'type' => DOKU_CHANGE_TYPE_EDIT,
            'sum'  => 'Test 2, 3rd save',
            'sizechange' => 0,
        );

        $pagelog = new PageChangeLog($page);
        $this->checkChangeLogAfterNormalSave($pagelog, $expectedRevs, $expect, $expectExternal);

        $this->waitForTick(); // wait for new revision ID

         // 2.5 external edit
        file_put_contents($file, 'teststring7 external edit2');
        clearstatcache(false, $file);
        $newmod = filemtime($file);
        $this->assertNotEquals($lastmod, $newmod);
        $lastmod = $newmod;
        $expectedRevs = 4; // external edit is not yet in changelog
        $expectExternal = array(
            'date' => $lastmod,
            'type' => DOKU_CHANGE_TYPE_EDIT,
            'sum'  => 'external edit',
            'sizechange' => 15,
        );

        $pagelog = new PageChangeLog($page);
        $this->checkChangeLogAfterExternalEdit($pagelog, $expectedRevs, $expect, $expectExternal);

        $this->waitForTick(); // wait for new revision ID

        // 2.6 save on top of external edit, again
        saveWikiText($page, 'teststring8', 'Test 2, 4th save', false);
        clearstatcache(false, $file);
        $newmod = filemtime($file);
        $this->assertNotEquals($lastmod, $newmod);
        $lastmod = $newmod;
        $expectedRevs = 6; // two more revisions now!
        $expect = array(
            'date' => $lastmod,
            'type' => DOKU_CHANGE_TYPE_EDIT,
            'sum'  => 'Test 2, 4th save',
            'sizechange' => -15,
        );

        $pagelog = new PageChangeLog($page);
        $this->checkChangeLogAfterNormalSave($pagelog, $expectedRevs, $expect, $expectExternal);
    }

    /**
     * Execute a whole bunch of saves on the same page and check the results
     * TEST 3 - typical page life of bundled page such as wiki:syntax
     *  3.1 externally create a page
     *  3.2 external edit
     *  3.3 edit and save on top of external edit
     *  3.4 externally delete the page
     */
    function test_savesequence3() {
        $page = 'page3';
        $file = wikiFN($page);

        // 3.1 externally create a page
        $this->assertFileNotExists($file);
        file_put_contents($file, 'teststring');
        clearstatcache(false, $file);
        $lastmod = filemtime($file);
        $expectedRevs = 0; // external edit is not yet in changelog
        $expect = false;
        $expectExternal = array(
            'date' => $lastmod,
            'type' => DOKU_CHANGE_TYPE_CREATE,
            'sum'  => 'created - external edit',
            'sizechange' => 10,
        );

        $pagelog = new PageChangeLog($page);
        $this->checkChangeLogAfterExternalEdit($pagelog, $expectedRevs, $expect, $expectExternal);

        $this->waitForTick(true); // wait for new revision ID

        // 3.2 external edit (repeated, still no changelog exists)
        file_put_contents($file, 'teststring external edit');
        clearstatcache(false, $file);
        $newmod = filemtime($file);
        $this->assertNotEquals($lastmod, $newmod);
        $lastmod = $newmod;
        $expectedRevs = 0; // external edit is not yet in changelog
        $expectExternal = array(
            'date' => $lastmod,
            'type' => DOKU_CHANGE_TYPE_CREATE,  // not DOKU_CHANGE_TYPE_EDIT
            'sum'  => 'created - external edit',
            'sizechange' => 24,
        );

        $pagelog = new PageChangeLog($page);
        $this->checkChangeLogAfterExternalEdit($pagelog, $expectedRevs, $expect, $expectExternal);

        $this->waitForTick(true); // wait for new revision ID

        // 3.3 save on top of external edit
        saveWikiText($page, 'teststring1', 'Test 3, first save', false);
        clearstatcache(false, $file);
        $newmod = filemtime($file);
        $this->assertNotEquals($lastmod, $newmod);
        $lastmod = $newmod;
        $expectedRevs = 2; // two more revisions now!
        $expect = array(
            'date' => $lastmod,
            'type' => DOKU_CHANGE_TYPE_EDIT,
            'sum'  => 'Test 3, first save',
            'sizechange' => -13,
        );

        $pagelog = new PageChangeLog($page);
        $this->checkChangeLogAfterNormalSave($pagelog, $expectedRevs, $expect, $expectExternal);

        $this->waitForTick(true); // wait for new revision ID

        // 3.4 externally delete the page
        unlink($file);
        $expectedRevs = 2;
        $expectExternal = array(
          //'date' => $lastmod,
            'type' => DOKU_CHANGE_TYPE_DELETE,
            'sum'  => 'removed - external edit (Unknown date)',
            'sizechange' => -11,
        );

        $pagelog = new PageChangeLog($page);
        $this->checkChangeLogAfterExternalEdit($pagelog, $expectedRevs, $expect, $expectExternal);
    }

    /**
     * Execute a whole bunch of saves on the same page and check the results
     * TEST 4 - typical page life of bundled page such as wiki:syntax
     *  4.1 externally create a page
     *  4.2 edit and save
     *  4.3 externally edit as a result of a file which has older timestamp than last revision
     */
    function test_savesequence4() {
        $page = 'page4';
        $file = wikiFN($page);

        // 4.1 externally create a page
        $this->assertFileNotExists($file);
        file_put_contents($file, 'teststring');
        clearstatcache(false, $file);
        $lastmod = filemtime($file);
        $expectedRevs = 0; // external edit is not yet in changelog
        $expect = false;
        $expectExternal = array(
            'date' => $lastmod,
            'type' => DOKU_CHANGE_TYPE_CREATE,
            'sum'  => 'created - external edit',
            'sizechange' => 10,
        );

        $pagelog = new PageChangeLog($page);
        $this->checkChangeLogAfterExternalEdit($pagelog, $expectedRevs, $expect, $expectExternal);

        $this->waitForTick(true); // wait for new revision ID

        // 4.2 edit and save
        saveWikiText($page, 'teststring1', 'Test 4, first save', false);
        clearstatcache(false, $file);
        $newmod = filemtime($file);
        $this->assertNotEquals($lastmod, $newmod);
        $lastmod = $newmod;
        $expectedRevs = 2; // two more revisions now!
        $expect = array(
            'date' => $lastmod,
            'type' => DOKU_CHANGE_TYPE_EDIT,
            'sum'  => 'Test 4, first save',
            'sizechange' => 1,
        );

        $pagelog = new PageChangeLog($page);
        $this->checkChangeLogAfterNormalSave($pagelog, $expectedRevs, $expect, $expectExternal);

        $this->waitForTick(true); // wait for new revision ID

        // 4.3 externally edit as a result of a file which has older timestamp than last revision
        unlink($file);
        file_put_contents($file, 'teststring fake 1 hout past');
        touch($file, filemtime($file) -3600); // change file modification time to 1 hour past
        clearstatcache();
        $newmod = filemtime($file);
        $this->assertLessThan($lastmod, $newmod); // file must be older than previous for this test
        $expectedRevs = 2; // external edit is not yet in changelog
        $expectExternal = array(
            'date' => $lastmod + 1,
            'type' => DOKU_CHANGE_TYPE_EDIT,
            'sum'  => 'external edit (Unknown date)',
            'sizechange' => 16,
        );

        $pagelog = new PageChangeLog($page);
        $this->checkChangeLogAfterExternalEdit($pagelog, $expectedRevs, $expect, $expectExternal);
    }

    /**
     * Execute a whole bunch of saves on the same page and check the results
     * TEST 5 - page creation and deletion
     *  5.1 create a page
     *  5.2 external edit
     *  5.3 edit and save on top of external edit
     *  5.4 delete
     *  5.5 create a page, second time
     *  5.6 externally delete
     *  5.7 create a page, third time
     */
    function test_savesequence5() {
        $page = 'page5';
        $file = wikiFN($page);
        $this->assertFileNotExists($file);

        // 5.1 create a page
        saveWikiText($page, 'teststring', 'Test 5, 1st save', false);
        $this->assertFileExists($file);
        $lastmod = filemtime($file);
        $expectedRevs = 1;
        $expect = array(
            'date' => $lastmod,
            'type' => DOKU_CHANGE_TYPE_CREATE,
            'sum'  => 'Test 5, 1st save',
            'sizechange' => 10, // = strlen('teststring')
        );

        $pagelog = new PageChangeLog($page);
        $this->checkChangeLogAfterNormalSave($pagelog, $expectedRevs, $expect);

        $this->waitForTick(true); // wait for new revision ID

        // 5.2 external edit
        file_put_contents($file, 'teststring external edit');
        clearstatcache(false, $file);
        $newmod = filemtime($file);
        $this->assertNotEquals($lastmod, $newmod);
        $lastmod = $newmod;
        $expectedRevs = 1; // external edit is not yet in changelog
        $expectExternal = array(
            'date' => $lastmod,
            'type' => DOKU_CHANGE_TYPE_EDIT,
            'sum'  => 'external edit',
            'sizechange' => 14,
        );

        $pagelog = new PageChangeLog($page);
        $this->checkChangeLogAfterExternalEdit($pagelog, $expectedRevs, $expect, $expectExternal);

        $this->waitForTick(); // wait for new revision ID

        // 5.3 edit and save on top of external edit
        saveWikiText($page, 'teststring normal edit', 'Test 5, 2nd save', false);
        clearstatcache(false, $file);
        $newmod = filemtime($file);
        $this->assertNotEquals($lastmod, $newmod);
        $lastmod = $newmod;
        $expectedRevs = 3; // two more revisions now!
        $expect = array(
            'date' => $lastmod,
            'type' => DOKU_CHANGE_TYPE_EDIT,
            'sum'  => 'Test 5, 2nd save',
            'sizechange' => -2,
        );

        $pagelog = new PageChangeLog($page);
        $this->checkChangeLogAfterNormalSave($pagelog, $expectedRevs, $expect, $expectExternal);

        $this->waitForTick(); // wait for new revision ID

        // 5.4 delete
        saveWikiText($page, '', 'Test 5 3rd save', false);
        clearstatcache(false, $file);
        $this->assertFileNotExists($file);
        $expectedRevs = 4;
        $expect = array(
          //'date' => $lastmod, // ignore from lastRev assertion, but confirm attic file existence
            'type' => DOKU_CHANGE_TYPE_DELETE,
            'sum'  => 'Test 5 3rd save',
            'sizechange' => -22,
        );

        $pagelog = new PageChangeLog($page);
        $this->checkChangeLogAfterNormalSave($pagelog, $expectedRevs, $expect);

        $this->waitForTick(); // wait for new revision ID

        // 5.5 create a page, second time
        $this->assertFileNotExists($file);
        saveWikiText($page, 'teststring revived', 'Test 5, 4th save', false);
        $this->assertFileExists($file);
        $lastmod = filemtime($file);
        $expectedRevs = 5;
        $expect = array(
            'date' => $lastmod,
            'type' => DOKU_CHANGE_TYPE_CREATE,
            'sum'  => 'Test 5, 4th save',
            'sizechange' => 18, // = strlen('teststring revived')
        );

        $pagelog = new PageChangeLog($page);
        $this->checkChangeLogAfterNormalSave($pagelog, $expectedRevs, $expect);

        $this->waitForTick(true); // wait for new revision ID

        // 5.6 externally delete
        unlink($file);
        $this->assertFileNotExists($file);
        $expectedRevs = 5;
        $expectExternal = array(
          //'date' => $lastmod,
            'type' => DOKU_CHANGE_TYPE_DELETE,
            'sum'  => 'removed - external edit (Unknown date)',
            'sizechange' => -18,
        );

        $pagelog = new PageChangeLog($page);
        $this->checkChangeLogAfterExternalEdit($pagelog, $expectedRevs, $expect, $expectExternal);

        $this->waitForTick(true); // wait for new revision ID

        // 5.7 create a page, third time
        $this->assertFileNotExists($file);
        saveWikiText($page, 'teststring revived 2', 'Test 5, 5th save', false);
        clearstatcache(false, $file);
        $this->assertFileExists($file);
        $lastmod = filemtime($file);
        $expectedRevs = 7;
        $expect = array(
            'date' => $lastmod,
            'type' => DOKU_CHANGE_TYPE_CREATE,
            'sum'  => 'Test 5, 5th save',
            'sizechange' => 20, // = strlen('teststring revived 2')
        );

        $pagelog = new PageChangeLog($page);
        $this->checkChangeLogAfterNormalSave($pagelog, $expectedRevs, $expect, $expectExternal);
    }

}
