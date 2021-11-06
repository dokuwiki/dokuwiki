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
     * TEST 1
     *  1.1 create a page
     *  1.2 save with same content should be ignored
     *  1.3 update the page with new text
     *  1.4 add a minor edit (unauthenticated)
     *  1.5 add a minor edit (authenticated)
     *  1.6 delete
     *  1.7 restore
     *  1.8 external edit
     *  1.9 save on top of external edit
     */
    function test_savesequence1() {
        global $REV;

        $page = 'page';
        $file = wikiFN($page);

        // 1.1 create a page
        $this->assertFileNotExists($file);
        saveWikiText($page, 'teststring', '1st save', false);
        $this->assertFileExists($file);
        $lastmod = filemtime($file);
        $expect = array(
            'date' => $lastmod,
            'type' => DOKU_CHANGE_TYPE_CREATE,
            'sum'  => '1st save',
            'sizechange' => 10, // = strlen('teststring')
        );

        $pagelog = new PageChangeLog($page);
        $revisions = $pagelog->getRevisions(-1, 200);
        $this->assertCount(1, $revisions);
        // last revision
        $lastRevInfo = $pagelog->getRevisionInfo($revisions[0]);
        $expect += $lastRevInfo;
        $this->assertEquals($expect, $lastRevInfo);
        // current revision
        $currentRevInfo = $pagelog->getCurrentRevisionInfo();
        $this->assertEquals($currentRevInfo, $lastRevInfo, 'current & last revs should be identical');
        // attic
        $this->assertFileExists(wikiFN($page, $lastRevInfo['date'], 'file missing in attic'));

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
        $expect = array(
            'date' => $lastmod,
            'type' => DOKU_CHANGE_TYPE_EDIT,
            'sum'  => '3rd save',
            'sizechange' => 5,
        );

        $pagelog = new PageChangeLog($page);
        $revisions = $pagelog->getRevisions(-1, 200);
        $this->assertCount(2, $revisions);
        // last revision
        $lastRevInfo = $pagelog->getRevisionInfo($revisions[0]);
        $expect += $lastRevInfo;
        $this->assertEquals($expect, $lastRevInfo);
        // current revision
        $currentRevInfo = $pagelog->getCurrentRevisionInfo();
        $this->assertEquals($currentRevInfo, $lastRevInfo, 'current & last revs should be identical');
        // attic
        $this->assertFileExists(wikiFN($page, $lastRevInfo['date'], 'file missing in attic'));

        $this->waitForTick(); // wait for new revision ID

        // 1.4 add a minor edit (unauthenticated)
        saveWikiText($page, 'teststring3long', '4th save', true);
        clearstatcache(false, $file);
        $newmod = filemtime($file);
        $this->assertNotEquals($lastmod, $newmod);
        $lastmod = $newmod;
        $expect = array(
            'date' => $lastmod,
            'type' => DOKU_CHANGE_TYPE_EDIT,
            'sum'  => '4th save',
            'sizechange' => 0,
        );

        $pagelog = new PageChangeLog($page);
        $revisions = $pagelog->getRevisions(-1, 200);
        $this->assertCount(3, $revisions);
        // last revision
        $lastRevInfo = $pagelog->getRevisionInfo($revisions[0]);
        $expect += $lastRevInfo;
        $this->assertEquals($expect, $lastRevInfo);
        // current revision
        $currentRevInfo = $pagelog->getCurrentRevisionInfo();
        $this->assertEquals($currentRevInfo, $lastRevInfo, 'current & last revs should be identical');
        // attic
        $this->assertFileExists(wikiFN($page, $lastRevInfo['date'], 'file missing in attic'));

        $this->waitForTick(); // wait for new revision ID

        // 1.5 add a minor edit (authenticated)
        $_SERVER['REMOTE_USER'] = 'user';
        saveWikiText($page, 'teststring4', '5th save', true);
        clearstatcache(false, $file);
        $newmod = filemtime($file);
        $this->assertNotEquals($lastmod, $newmod);
        $lastmod = $newmod;
        $expect = array(
            'date' => $lastmod,
            'type' => DOKU_CHANGE_TYPE_MINOR_EDIT,
            'sum'  => '5th save',
            'sizechange' => -4,
        );

        $pagelog = new PageChangeLog($page);
        $revisions = $pagelog->getRevisions(-1, 200);
        $this->assertCount(4, $revisions);
        // last revision
        $lastRevInfo = $pagelog->getRevisionInfo($revisions[0]);
        $expect += $lastRevInfo;
        $this->assertEquals($expect, $lastRevInfo);
        // current revision
        $currentRevInfo = $pagelog->getCurrentRevisionInfo();
        $this->assertEquals($currentRevInfo, $lastRevInfo, 'current & last revs should be identical');
        // attic
        $this->assertFileExists(wikiFN($page, $lastRevInfo['date'], 'file missing in attic'));

        $this->waitForTick(); // wait for new revision ID

        // 1.6 delete
        saveWikiText($page, '', '6th save', false);
        clearstatcache(false, $file);
        $this->assertFileNotExists($file);
        $expect = array(
          //'date' => $lastmod, // ignore from lastRev assertion, but confirm attic file existence
            'type' => DOKU_CHANGE_TYPE_DELETE,
            'sum'  => '6th save',
            'sizechange' => -11,
        );

        $pagelog = new PageChangeLog($page);
        $revisions = $pagelog->getRevisions(-1, 200);
        $this->assertCount(5, $revisions);
        // last revision
        $lastRevInfo = $pagelog->getRevisionInfo($revisions[0]);
        $expect += $lastRevInfo;
        $this->assertEquals($expect, $lastRevInfo);
        // current revision
        $currentRevInfo = $pagelog->getCurrentRevisionInfo();
        $this->assertEquals($currentRevInfo, $lastRevInfo, 'current & last revs should be identical');
        // attic
        $this->assertFileExists(wikiFN($page, $lastRevInfo['date'], 'file missing in attic'));

        $this->waitForTick(); // wait for new revision ID

        // 1.7 restore
        $REV = $lastmod;
        saveWikiText($page, 'teststring4', '7th save', true);
        clearstatcache(false, $file);
        $this->assertFileExists($file);
        $newmod = filemtime($file);
        $this->assertNotEquals($lastmod, $newmod);
        $lastmod = $newmod;
        $expect = array(
            'date' => $lastmod,
            'type' => DOKU_CHANGE_TYPE_REVERT,
            'sum'  => '7th save',
            'sizechange' => 11,
        );

        $pagelog = new PageChangeLog($page);
        $revisions = $pagelog->getRevisions(-1, 200);
        $this->assertCount(6, $revisions);
        // last revision
        $lastRevInfo = $pagelog->getRevisionInfo($revisions[0]);
        $expect += $lastRevInfo;
        $this->assertEquals($expect, $lastRevInfo);
        // current revision
        $currentRevInfo = $pagelog->getCurrentRevisionInfo();
        $this->assertEquals($currentRevInfo, $lastRevInfo, 'current & last revs should be identical');
        // attic
        $this->assertFileExists(wikiFN($page, $lastRevInfo['date']), 'file missing in attic');
        $files = glob(dirname(wikiFN($page, $lastRevInfo['date'])).'/'.$page.'.*');
        $this->assertCount(6, $files, 'detectExternalEdit() should not add too often old revs');
        $REV = '';

        $this->waitForTick(); // wait for new revision ID

        // 1.8 external edit
        file_put_contents($file, 'teststring5 external edit');
        clearstatcache(false, $file);
        $newmod = filemtime($file);
        $this->assertNotEquals($lastmod, $newmod);
        $lastmod = $newmod;
        $expectExternal = array(
            'date' => $lastmod,
            'type' => DOKU_CHANGE_TYPE_EDIT,
            'sum'  => 'external edit',
            'sizechange' => 14,
        );

        $pagelog = new PageChangeLog($page);
        $revisions = $pagelog->getRevisions(-1, 200);
        $this->assertCount(6, $revisions); // external edit is not yet in changelog
        // last revision
        $lastRevInfo = $pagelog->getRevisionInfo($revisions[0]);
        $this->assertEquals($expect, $lastRevInfo);
        // current revision
        $currentRevInfo = $pagelog->getCurrentRevisionInfo();
        $this->assertArrayHasKey('timestamp', $currentRevInfo, 'should be external revision');
        $expectExternal += $currentRevInfo;
        $this->assertEquals($expectExternal, $currentRevInfo);
        // attic
        $this->assertFileExists(wikiFN($page, $lastRevInfo['date']), 'file missing in attic');
        $this->assertFileNotExists(wikiFN($page, $currentRevInfo['date']),'page does not yet exist in attic');

        $this->waitForTick(); // wait for new revision ID

        // 1.9 save on top of external edit
        saveWikiText($page, 'teststring6', '8th save', false);
        clearstatcache(false, $file);
        $newmod = filemtime($file);
        $this->assertNotEquals($lastmod, $newmod);
        $lastmod = $newmod;
        $expect = array(
            'date' => $lastmod,
            'type' => DOKU_CHANGE_TYPE_EDIT,
            'sum'  => '8th save',
            'sizechange' => -14,
        );

        $pagelog = new PageChangeLog($page);
        $revisions = $pagelog->getRevisions(-1, 200);
        $this->assertCount(8, $revisions); // two more revisions now!
        // last revision
        $lastRevInfo = $pagelog->getRevisionInfo($revisions[0]);
        $expect += $lastRevInfo;
        $this->assertEquals($expect, $lastRevInfo);
        // current revision
        $currentRevInfo = $pagelog->getCurrentRevisionInfo();
        $this->assertEquals($currentRevInfo, $lastRevInfo, 'current & last revs should be identical');
        // attic
        $this->assertFileExists(wikiFN($page, $lastRevInfo['date'], 'file missing in attic'));
        $files = glob(dirname(wikiFN($page, $lastRevInfo['date'])).'/'.$page.'.*');
        $this->assertCount(8, $files, 'detectExternalEdit() should not add too often old revs');

    }

    /**
     * Execute a whole bunch of saves on the same page and check the results
     * using $this->handle_write() in event IO_WIKIPAGE_WRITE
     * TEST 2 - create a page externally, while external edit in Test 1
     *  2.1 create a page
     *  2.2 delete
     *  2.3 externally create the page
     *  2.4 save on top of external edit
     */
    function test_savesequence2() {
        // add an additional delay when saving files to make sure
        // nobody relies on the saving happening in the same second
        /** @var $EVENT_HANDLER \dokuwiki\Extension\EventHandler */
        global $EVENT_HANDLER;
        $EVENT_HANDLER->register_hook('IO_WIKIPAGE_WRITE', 'BEFORE', $this, 'handle_write');

        $page = 'page2';
        $file = wikiFN($page);

        // 2.1 create a page
        $this->assertFileNotExists($file);
        saveWikiText($page, 'teststring', 'Test 2, 1st save', false);
        $this->assertFileExists($file);
        $lastmod = filemtime($file);
        $expect = array(
            'date' => $lastmod,
            'type' => DOKU_CHANGE_TYPE_CREATE,
            'sum'  => 'Test 2, 1st save',
            'sizechange' => 10, // = strlen('teststring')
        );

        $pagelog = new PageChangeLog($page);
        $revisions = $pagelog->getRevisions(-1, 200);
        $this->assertCount(1, $revisions);
        // last revision
        $lastRevInfo = $pagelog->getRevisionInfo($revisions[0]);
        $expect += $lastRevInfo;
        $this->assertEquals($expect, $lastRevInfo);
        // current revision
        $currentRevInfo = $pagelog->getCurrentRevisionInfo();
        $this->assertEquals($currentRevInfo, $lastRevInfo, 'current & last revs should be identical');
        // attic
        $this->assertFileExists(wikiFN($page, $lastRevInfo['date'], 'file missing in attic'));

        $this->waitForTick(true); // wait for new revision ID

        // 2.2 delete
        saveWikiText($page, '', 'Test 2, 2nd save', false);
        clearstatcache(false, $file);
        $this->assertFileNotExists($file);
        $expect = array(
          //'date' => $lastmod, // ignore from lastRev assertion, but confirm attic file existence
            'type' => DOKU_CHANGE_TYPE_DELETE,
            'sum'  => 'Test 2, 2nd save',
            'sizechange' => -10,
        );

        $pagelog = new PageChangeLog($page);
        $revisions = $pagelog->getRevisions(-1, 200);
        $this->assertCount(2, $revisions);
        // last revision
        $lastRevInfo = $pagelog->getRevisionInfo($revisions[0]);
        $expect += $lastRevInfo;
        $this->assertEquals($expect, $lastRevInfo);
        // current revision
        $currentRevInfo = $pagelog->getCurrentRevisionInfo();
        $this->assertEquals($currentRevInfo, $lastRevInfo, 'current & last revs should be identical');
        // attic
        $this->assertFileExists(wikiFN($page, $lastRevInfo['date'], 'file missing in attic'));

        $this->waitForTick(); // wait for new revision ID

        // 2.3 externally create the page
        file_put_contents($file, 'teststring5');
        clearstatcache(false, $file);
        $lastmod = filemtime($file);
        $expectExternal = array(
            'date' => $lastmod,
            'type' => DOKU_CHANGE_TYPE_CREATE,
            'sum'  => 'created - external edit',
            'sizechange' => 11,
        );

        $pagelog = new PageChangeLog($page);
        $revisions = $pagelog->getRevisions(-1, 200);
        $this->assertCount(2, $revisions); // external edit is not yet in changelog
        // last revision
        $lastRevInfo = $pagelog->getRevisionInfo($revisions[0]);
        $this->assertEquals($expect, $lastRevInfo);
        // current revision
        $currentRevInfo = $pagelog->getCurrentRevisionInfo();
        $this->assertArrayHasKey('timestamp', $currentRevInfo, 'should be external revision');
        $expectExternal += $currentRevInfo;
        $this->assertEquals($expectExternal, $currentRevInfo);
        // attic
        $this->assertFileExists(wikiFN($page, $lastRevInfo['date']), 'file missing in attic');
        $this->assertFileNotExists(wikiFN($page, $currentRevInfo['date']),'page does not yet exist in attic');

        $this->waitForTick(); // wait for new revision ID

        // 2.4 save on top of external edit
        saveWikiText($page, 'teststring6', 'Test 2, 3rd save', false);
        clearstatcache(false, $file);
        $newmod = filemtime($file);
        $this->assertNotEquals($lastmod, $newmod);
        $lastmod = $newmod;
        $expect = array(
            'date' => $lastmod,
            'type' => DOKU_CHANGE_TYPE_EDIT,
            'sum'  => 'Test 2, 3rd save',
            'sizechange' => 0,
        );

        $pagelog = new PageChangeLog($page);
        $revisions = $pagelog->getRevisions(-1, 200);
        $this->assertCount(4, $revisions); // two more revisions now!
        // last revision
        $lastRevInfo = $pagelog->getRevisionInfo($revisions[0]);
        $expect += $lastRevInfo;
        $this->assertEquals($expect, $lastRevInfo);
        // current revision
        $currentRevInfo = $pagelog->getCurrentRevisionInfo();
        $this->assertEquals($currentRevInfo, $lastRevInfo, 'current & last revs should be identical');
        // attic
        $this->assertFileExists(wikiFN($page, $lastRevInfo['date'], 'file missing in attic'));
        $files = glob(dirname(wikiFN($page, $lastRevInfo['date'])).'/'.$page.'.*');
        $this->assertCount(4, $files, 'detectExternalEdit() should not add too often old revs');

    }

    /**
     * Execute a whole bunch of saves on the same page and check the results
     * TEST 3 - typical page life of bundled page such as wiki/syntax
     *  3.1 externally create a page
     *  3.2 external edit
     *  3.3 save on top of external edit
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
        $expectExternal = array(
            'date' => $lastmod,
            'type' => DOKU_CHANGE_TYPE_CREATE,
            'sum'  => 'created - external edit',
            'sizechange' => 10,
        );

        $pagelog = new PageChangeLog($page);
        $revisions = $pagelog->getRevisions(-1, 200);
        $this->assertCount(0, $revisions); // external edit is not yet in changelog
        // last revision
        $this->assertFalse($pagelog->lastRevision(), 'changelog file does not yet exist');
        // current revision
        $currentRevInfo = $pagelog->getCurrentRevisionInfo();
        $this->assertArrayHasKey('timestamp', $currentRevInfo, 'should be external revision');
        $expectExternal += $currentRevInfo;
        $this->assertEquals($expectExternal, $currentRevInfo);
        // attic
        $this->assertFileNotExists(wikiFN($page, $currentRevInfo['date']),'page does not yet exist in attic');

        $this->waitForTick(true); // wait for new revision ID

        // 3.2 external edit
        file_put_contents($file, 'teststring external edit');
        clearstatcache(false, $file);
        $newmod = filemtime($file);
        $this->assertNotEquals($lastmod, $newmod);
        $lastmod = $newmod;
        $expectExternal = array(
            'date' => $lastmod,
            'type' => DOKU_CHANGE_TYPE_CREATE,
            'sum'  => 'created - external edit',
            'sizechange' => 24,
        );

        $pagelog = new PageChangeLog($page);
        $revisions = $pagelog->getRevisions(-1, 200);
        $this->assertCount(0, $revisions); // external edit is not yet in changelog
        // last revision
        $this->assertFalse($pagelog->lastRevision(), 'changelog file does not yet exist');
        // current revision
        $currentRevInfo = $pagelog->getCurrentRevisionInfo();
        $this->assertArrayHasKey('timestamp', $currentRevInfo, 'should be external revision');
        $expectExternal += $currentRevInfo;
        $this->assertEquals($expectExternal, $currentRevInfo);
        // attic
        $this->assertFileNotExists(wikiFN($page, $currentRevInfo['date']),'page does not yet exist in attic');

        $this->waitForTick(true); // wait for new revision ID

        // 3.3 save on top of external edit
        saveWikiText($page, 'teststring1', 'Test 3, first save', false);
        clearstatcache(false, $file);
        $newmod = filemtime($file);
        $this->assertNotEquals($lastmod, $newmod);
        $lastmod = $newmod;
        $expect = array(
            'date' => $lastmod,
            'type' => DOKU_CHANGE_TYPE_EDIT,
            'sum'  => 'Test 3, first save',
            'sizechange' => -13,
        );

        $pagelog = new PageChangeLog($page);
        $revisions = $pagelog->getRevisions(-1, 200);
        $this->assertCount(2, $revisions); // two more revisions now!
        // last revision
        $lastRevInfo = $pagelog->getRevisionInfo($revisions[0]);
        $expect += $lastRevInfo;
        $this->assertEquals($expect, $lastRevInfo);
        // current revision
        $currentRevInfo = $pagelog->getCurrentRevisionInfo();
        $this->assertEquals($currentRevInfo, $lastRevInfo, 'current & last revs should be identical');
        // attic
        $this->assertFileExists(wikiFN($page, $lastRevInfo['date'], 'file missing in attic'));
        $files = glob(dirname(wikiFN($page, $lastRevInfo['date'])).'/'.$page.'.*');
        $this->assertCount(2, $files, 'detectExternalEdit() should not add too often old revs');

        $this->waitForTick(true); // wait for new revision ID

        // 3.4 externally delete the page
        unlink($file);
        clearstatcache(false, $file);
        $expectExternal = array(
          //'date' => $lastmod,
            'type' => DOKU_CHANGE_TYPE_DELETE,
            'sum'  => 'removed - external edit (Unknown date)',
            'sizechange' => -11,
        );

        $pagelog = new PageChangeLog($page);
        $revisions = $pagelog->getRevisions(-1, 200);
        $this->assertCount(2, $revisions); // two more revisions now!
        // last revision
        $lastRevInfo = $pagelog->getRevisionInfo($revisions[0]);
        $this->assertEquals($expect, $lastRevInfo);
        // current revision
        $currentRevInfo = $pagelog->getCurrentRevisionInfo();
        $this->assertArrayHasKey('timestamp', $currentRevInfo, 'should be external revision');
        $expectExternal += $currentRevInfo;
        $this->assertEquals($expectExternal, $currentRevInfo);
        // attic
        $this->assertFileNotExists(wikiFN($page, $currentRevInfo['date']),'page does not yet exist in attic');

    }

}
