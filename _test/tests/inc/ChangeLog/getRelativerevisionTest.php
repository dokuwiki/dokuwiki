<?php

namespace tests\inc\ChangeLog;

use dokuwiki\ChangeLog\PageChangeLog;

/**
 * Tests for requesting revisioninfo of a revision of a page with getRevisionInfo()
 *
 * This class uses the files:
 * - data/pages/mailinglist.txt
 * - data/meta/mailinglist.changes
 */
class GetRelativeRevisionTest extends \DokuWikiTest {

    private $logline = "1362525899	127.0.0.1	E	mailinglist	pubcie	[Data entry] 	\n";
    private $pageid = 'mailinglist';

    function setup() : void {
        parent::setup();
        global $cache_revinfo;
        $cache =& $cache_revinfo;
        unset($cache['nonexist']);
        unset($cache['mailinglist']);
    }

    /**
     * not available nonexist.changes meta file
     */
    function test_ChangeMetadataNotExists() {
        $rev = 1362525899;  // arbitrary number
        $direction = 1;
        $id = 'nonexist';

        $pagelog = new PageChangeLog($id, $chunk_size = 8192);
        $revfound = $pagelog->getRelativeRevision($rev, $direction);
        $this->assertFalse($revfound);
    }

    /**
     * not available nonexist.changes meta file
     */
    function test_NoDirection() {
        $rev = 1362525899;  // arbitrary number
        $direction = 0;

        $pagelog = new PageChangeLog($this->pageid, $chunk_size = 8192);
        $revfound = $pagelog->getRelativeRevision($rev, $direction);
        $this->assertFalse($revfound);
    }

    /**
     * start at exact current revision of mailinglist page
     *
     */
    function test_StartAtExactCurrentRev() {
        $rev = 1385051947;  // newer than last rev in mailinglist.change file
        $direction = 1;
        $revexpectedpos = false;
        $revexpectedneg = 1374261194;  // found at line 24, last revision

        //set a known timestamp to emulate external edit
        touch(wikiFN($this->pageid), $rev);

        $pagelog = new PageChangeLog($this->pageid, $chunk_size = 8192);
        $revfound = $pagelog->getRelativeRevision($rev, $direction);
        $this->assertEquals($revexpectedpos, $revfound);

        $revfound = $pagelog->getRelativeRevision($rev, -$direction);
        $this->assertEquals($revexpectedneg, $revfound);
    }

    /**
     * start at exact first revision of mailinglist page
     *
     */
    function test_StartAtExactFirstRev() {
        $rev = 1360110636;  // found at line 1 of changelog
        $direction = 1;
        $revexpectedpos = 1361901536;  // found at line 2
        $revexpectedneg = false;

        $pagelog = new PageChangeLog($this->pageid, $chunk_size = 8192);
        $revfound = $pagelog->getRelativeRevision($rev, $direction);
        $this->assertEquals($revexpectedpos, $revfound);

        $revfound = $pagelog->getRelativeRevision($rev, -$direction);
        $this->assertEquals($revexpectedneg, $revfound);
    }

    /**
     * start at exact one before first revision of mailinglist page
     *
     */
    function test_RequestFirstRev() {
        $rev = 1361901536;  // found at line 2 of changelog
        $direction = -1;
        $revexpectedlast = 1360110636; // found at line 1
        $revexpectedbeforelast = false;

        $pagelog = new PageChangeLog($this->pageid, $chunk_size = 8192);
        $revfound = $pagelog->getRelativeRevision($rev, $direction);
        $this->assertEquals($revexpectedlast, $revfound);

        $revfound = $pagelog->getRelativeRevision($rev, 2 * $direction);
        $this->assertEquals($revexpectedbeforelast, $revfound);
    }

    /**
     * request existing rev and check cache
     */
    function test_RequestRev_CheckCache() {
        $rev = 1362525359;  // found at line 5 of changelog
        $direction = 1;
        $revexpected = 1362525899;  // found at line 6
        $infoexpected = parseChangelogLine($this->logline);

        $pagelog = new PageChangeLog($this->pageid, $chunk_size = 8192);
        $revfound = $pagelog->getRelativeRevision($rev, $direction);
        $this->assertEquals($revexpected, $revfound);

        //checked info returned from cache
        $info = $pagelog->getRevisionInfo($revfound);
        $this->assertEquals($infoexpected, $info);
    }

    /**
     * request existing rev
     */
    function test_RequestNextRev() {
        $rev = 1362525899;  // found at line 6 of changelog

        $pagelog = new PageChangeLog($this->pageid, $chunk_size = 8192);

        $direction = 1;
        $revexpected = 1362525926;  // found at line 7
        $revfound = $pagelog->getRelativeRevision($rev, $direction);
        $this->assertEquals($revexpected, $revfound);

        $direction = 2;
        $revexpected = 1362526039;  // found at line 8
        $revfound = $pagelog->getRelativeRevision($rev, $direction);
        $this->assertEquals($revexpected, $revfound);

        $direction = -1;
        $revexpected = 1362525359;  // found at line 5
        $revfound = $pagelog->getRelativeRevision($rev, $direction);
        $this->assertEquals($revexpected, $revfound);

        $direction = -2;
        $revexpected = 1362525145;  // found at line 4
        $revfound = $pagelog->getRelativeRevision($rev, $direction);
        $this->assertEquals($revexpected, $revfound);
    }

    /**
     * request existing rev with chucked reading
     */
    function test_RequestNextRev_Chuncked() {
        $rev = 1362525899;  // found at line 6 of changelog

        $pagelog = new PageChangeLog($this->pageid, $chunk_size = 512);

        $direction = 1;
        $revexpected = 1362525926;  // found at line 7
        $revfound = $pagelog->getRelativeRevision($rev, $direction);
        $this->assertEquals($revexpected, $revfound);

        $direction = 2;
        $revexpected = 1362526039;  // found at line 8
        $revfound = $pagelog->getRelativeRevision($rev, $direction);
        $this->assertEquals($revexpected, $revfound);

        $direction = -1;
        $revexpected = 1362525359;  // found at line 5
        $revfound = $pagelog->getRelativeRevision($rev, $direction);
        $this->assertEquals($revexpected, $revfound);

        $direction = -2;
        $revexpected = 1362525145;  // found at line 4
        $revfound = $pagelog->getRelativeRevision($rev, $direction);
        $this->assertEquals($revexpected, $revfound);
    }


    /**
     * request existing rev with chucked reading, chunk size smaller than line length
     */
    function test_RequestNextRev_ChunkShorterThanLines() {
        $rev = 1362525899;  // found at line 6 of changelog

        $pagelog = new PageChangeLog($this->pageid, $chunk_size = 20);

        $direction = 1;
        $revexpected = 1362525926;  // found at line 7
        $revfound = $pagelog->getRelativeRevision($rev, $direction);
        $this->assertEquals($revexpected, $revfound);

        $direction = 2;
        $revexpected = 1362526039;  // found at line 8
        $revfound = $pagelog->getRelativeRevision($rev, $direction);
        $this->assertEquals($revexpected, $revfound);

        $direction = -1;
        $revexpected = 1362525359;  // found at line 5
        $revfound = $pagelog->getRelativeRevision($rev, $direction);
        $this->assertEquals($revexpected, $revfound);

        $direction = -2;
        $revexpected = 1362525145;  // found at line 4
        $revfound = $pagelog->getRelativeRevision($rev, $direction);
        $this->assertEquals($revexpected, $revfound);
    }

    /**
     * request existing rev
     */
    function test_RequestNextFifthRev() {
        $rev = 1362525899;  // found at line 6 of changelog
        $direction = 5;
        $revexpected = 1362526767;  // found at line 11

        $pagelog = new PageChangeLog($this->pageid, $chunk_size = 8192);
        $revfound = $pagelog->getRelativeRevision($rev, $direction);
        $this->assertEquals($revexpected, $revfound);
    }

    /**
     * request existing rev with chucked reading
     */
    function test_RequestNextFifthRev_Chuncked() {
        $rev = 1362525899;  // found at line 6 of changelog
        $direction = 5;
        $revexpected = 1362526767;  // found at line 11

        $pagelog = new PageChangeLog($this->pageid, $chunk_size = 512);
        $revfound = $pagelog->getRelativeRevision($rev, $direction);
        $this->assertEquals($revexpected, $revfound);
    }

    /**
     * request existing rev
     */
    function test_RequestPrevRev() {
        $rev = 1362525899;  // found at line 6 of changelog
        $dir1 = -1;
        $dir5 = -5;
        $revexpected1 = 1362525359; // found at line 5
        $revexpected5 = 1360110636; // found at line 1

        $pagelog = new PageChangeLog($this->pageid, $chunk_size = 8192);
        $revfound1 = $pagelog->getRelativeRevision($rev, $dir1);
        $this->assertEquals($revexpected1, $revfound1);

        $revfound5 = $pagelog->getRelativeRevision($rev, $dir5);
        $this->assertEquals($revexpected5, $revfound5);
    }

    /**
     * request existing rev with chucked reading
     */
    function test_RequestPrevRev_Chuncked() {
        $rev = 1362525899;  // found at line 6 of changelog
        $dir1 = -1;
        $dir5 = -5;
        $revexpected1 = 1362525359; // found at line 5
        $revexpected5 = 1360110636; // found at line 1

        $pagelog = new PageChangeLog($this->pageid, $chunk_size = 512);
        $revfound1 = $pagelog->getRelativeRevision($rev, $dir1);
        $this->assertEquals($revexpected1, $revfound1);

        $revfound5 = $pagelog->getRelativeRevision($rev, $dir5);
        $this->assertEquals($revexpected5, $revfound5);
    }

    /**
     * request after most recent version in changelog
     */
    function test_RequestRecentestLogline_Next() {
        $rev = 1374261194;  // found at line 24 of changelog
        $direction = 1;
        $revexpected = 1385051947; // external revision set at test_StartAtExactCurrentRev()

        $pagelog = new PageChangeLog($this->pageid, $chunk_size = 8192);
        $revfound = $pagelog->getRelativeRevision($rev, $direction);
        $this->assertEquals($revexpected, $revfound);
    }

    /**
     * request after most recent version in changelog, with chuncked reading
     */
    function test_RequestRecentestLogline_Next_Chuncked() {
        $rev = 1374261194;  // found at line 24 of changelog
        $direction = 1;
        $revexpected = 1385051947; // external revision set at test_StartAtExactCurrentRev()

        $pagelog = new PageChangeLog($this->pageid, $chunk_size = 512);
        $revfound = $pagelog->getRelativeRevision($rev, $direction);
        $this->assertEquals($revexpected, $revfound);
    }

    /**
     * request before current version
     */
    function test_RequestRecentestLogline_Prev() {
        $rev = 1374261194;  // found at line 24 of changelog
        $direction = -1;
        $revexpected = 1371579614;  // found at line 23

        $pagelog = new PageChangeLog($this->pageid, $chunk_size = 8192);
        $revfound = $pagelog->getRelativeRevision($rev, $direction);
        $this->assertEquals($revexpected, $revfound);
    }

    /**
     * request before current version, with chuncked reading
     */
    function test_RequestRecentestLogline_Prev_Chuncked() {
        $rev = 1374261194;  // found at line 24 of changelog
        $direction = -1;
        $revexpected = 1371579614;  // found at line 23

        $pagelog = new PageChangeLog($this->pageid, $chunk_size = 512);
        $revfound = $pagelog->getRelativeRevision($rev, $direction);
        $this->assertEquals($revexpected, $revfound);
    }

    /**
     * Request negative revision
     * looks in positive direction, so it catches the oldest revision
     */
    function test_NegativeRev_PosDir() {
        $rev = -10;
        $direction = 1;
        $revexpected = 1360110636;  // found at line 1

        $pagelog = new PageChangeLog($this->pageid, $chunk_size = 8192);
        $revfound = $pagelog->getRelativeRevision($rev, $direction);
        $this->assertEquals($revexpected, $revfound);
    }

    /**
     * Request negative revision
     * looks in negative direction, but there is nothing
     */
    function test_NegativeRev_NegDir() {
        $rev = -10;
        $direction = -1;
        $revexpected = false;

        $pagelog = new PageChangeLog($this->pageid, $chunk_size = 8192);
        $revfound = $pagelog->getRelativeRevision($rev, $direction);
        $this->assertEquals($revexpected, $revfound);
    }

    /**
     * Start at non existing revision somewhere between existing revisions
     */
    function test_StartAtNotExistingRev_Next() {
        $rev = 1362525890;  // between line 5 and 6
        $direction = 1;
        $revexpected = 1362525899;  // found at line 6

        $pagelog = new PageChangeLog($this->pageid, $chunk_size = 8192);
        $revfound = $pagelog->getRelativeRevision($rev, $direction);
        $this->assertEquals($revexpected, $revfound);
    }

    /**
     * Start at non existing revision somewhere between existing revisions
     */
    function test_StartAtNotExistingRev_Prev() {
        $rev = 1362525890;  // between line 5 and 6
        $direction = -1;
        $revexpected = 1362525359;  // found at line 5

        $pagelog = new PageChangeLog($this->pageid, $chunk_size = 8192);
        $revfound = $pagelog->getRelativeRevision($rev, $direction);
        $this->assertEquals($revexpected, $revfound);
    }


    /**
     * check whether a number is current revision of exsisting page
     */
    function test_IsCurrentPageRevision() {
        $rev = 1385051947;  // newer than last rev in mailinglist.change file
        $currentexpected = true;

        //set a known timestamp
        touch(wikiFN($this->pageid), $rev);

        $pagelog = new PageChangeLog($this->pageid, $chunk_size = 8192);
        $current = $pagelog->isCurrentRevision($rev);
        $this->assertEquals($currentexpected, $current);
    }

    /**
     * check whether a number is not current revision of exsisting page
     */
    function test_IsNotCurrentPageRevision() {
        $rev = 1385051947;  // newer than last rev in mailinglist.change file
        $not_current_rev = $rev - 1;
        $currentexpected = false;

        //set a known timestamp
        touch(wikiFN($this->pageid), $rev);

        $pagelog = new PageChangeLog($this->pageid, $chunk_size = 8192);
        $current = $pagelog->isCurrentRevision($not_current_rev);
        $this->assertEquals($currentexpected, $current);
    }

    /**
     * check whether a number is current revision of non-exsisting page
     */
    function test_NotExistingCurrentPage() {
        $rev = 1385051947;  // newer than last rev in mailinglist.change file
        $currentexpected = false;

        $pagelog = new PageChangeLog('nonexistingpage', $chunk_size = 8192);
        $current = $pagelog->isCurrentRevision($rev);
        $this->assertEquals($currentexpected, $current);
    }
}
