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
class getRevisionInfoTest extends \DokuWikiTest {

    private $logline = "1362525899	127.0.0.1	E	mailinglist	pubcie	[Data entry] 	\n";
    private $firstlogline = "1374261194	127.0.0.1	E	mailinglist	pubcie		\n";
    private $pageid = 'mailinglist';

    function setup() : void {
        parent::setup();
        global $cache_revinfo;
        $cache =& $cache_revinfo;
        unset($cache['nonexist']);
        unset($cache['mailinglist']);
    }

    /**
     * no nonexist.changes meta file available
     */
    function testChangeMetadataNotExists() {
        $rev = 1362525899;
        $id = 'nonexist';
        $revsexpected = false;

        $pagelog = new PageChangeLog($id, $chunk_size = 8192);
        $revs = $pagelog->getRevisionInfo($rev);
        $this->assertEquals($revsexpected, $revs);
    }

    /**
     * request existing rev
     */
    function testRequestRev() {
        $rev = 1362525899;
        $infoexpected = parseChangelogLine($this->logline);

        $pagelog = new PageChangeLog($this->pageid, $chunk_size = 8192);
        $info = $pagelog->getRevisionInfo($rev);
        $this->assertEquals($infoexpected, $info);
        //returns cached value
        $info = $pagelog->getRevisionInfo($rev);
        $this->assertEquals($infoexpected, $info);
    }

    /**
     * request existing rev with chucked reading
     */
    function testRequestRev_Chuncked() {
        $rev = 1362525899;
        $infoexpected = parseChangelogLine($this->logline);

        $pagelog = new PageChangeLog($this->pageid, $chunk_size = 512);
        $info = $pagelog->getRevisionInfo($rev);
        $this->assertEquals($infoexpected, $info);
    }

    /**
     * request existing rev with chucked reading
     */
    function testRequestRev_ChunckedSmallerThanLineLength() {
        $rev = 1362525899;
        $infoexpected = parseChangelogLine($this->logline);

        $pagelog = new PageChangeLog($this->pageid, $chunk_size = 20);
        $info = $pagelog->getRevisionInfo($rev);
        $this->assertEquals($infoexpected, $info);
    }

    /**
     * request current version
     */
    function testRequestRecentestLogLine() {
        $rev = 1374261194;
        $infoexpected = parseChangelogLine($this->firstlogline);

        $pagelog = new PageChangeLog($this->pageid, $chunk_size = 8192);
        $info = $pagelog->getRevisionInfo($rev);
        $this->assertEquals($infoexpected, $info);
        //returns cached value
        $info = $pagelog->getRevisionInfo($rev);
        $this->assertEquals($infoexpected, $info);
    }

    /**
     * request current version, with chuncked reading
     */
    function testRequestRecentestLogLine_Chuncked() {
        $rev = 1374261194;
        $infoexpected = parseChangelogLine($this->firstlogline);

        $pagelog = new PageChangeLog($this->pageid, $chunk_size = 512);
        $info = $pagelog->getRevisionInfo($rev);
        $this->assertEquals($infoexpected, $info);
    }

    /**
     * request negative revision
     */
    function testNegativeRev() {
        $rev = -10;

        $pagelog = new PageChangeLog($this->pageid, $chunk_size = 8192);
        $info = $pagelog->getRevisionInfo($rev);
        $this->assertEquals(false, $info);
    }

    /**
     * request non existing revision somewhere between existing revisions
     */
    function testNotExistingRev() {
        $rev = 1362525890;

        $pagelog = new PageChangeLog($this->pageid, $chunk_size = 8192);
        $info = $pagelog->getRevisionInfo($rev);
        $this->assertEquals(false, $info);
    }

    /**
     * sometimes chuncksize is set to true
     */
    function testChunckSizeTrue() {
        $rev = 1362525899;
        $infoexpected = parseChangelogLine($this->logline);

        $pagelog = new PageChangeLog($this->pageid, true);
        $info = $pagelog->getRevisionInfo($rev);
        $this->assertEquals($infoexpected, $info);
    }
}
