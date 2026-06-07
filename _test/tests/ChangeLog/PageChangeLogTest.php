<?php

namespace dokuwiki\test\ChangeLog;

use dokuwiki\ChangeLog\PageChangeLog;

/**
 * Tests for dokuwiki\ChangeLog\PageChangeLog.
 */
class PageChangeLogTest extends \DokuWikiTest
{
    /**
     * A page deleted through DokuWiki is recorded as its own revision, newer than the
     * last revision that still had content. getRelativeRevision() must walk back from
     * that deletion entry to the last content revision (issue #4635).
     */
    public function testRevisionBeforeNormalDeletion()
    {
        $page = 'changelog_deleted';
        saveWikiText($page, 'first content', 'create', false);
        $this->waitForTick(true);
        saveWikiText($page, 'second content longer', 'edit', false);
        $this->waitForTick(true);

        $editRev = (new PageChangeLog($page))->currentRevision();

        saveWikiText($page, '', 'delete', false);
        clearstatcache();

        $changelog = new PageChangeLog($page);
        $delRev = $changelog->currentRevision();

        $this->assertNotEquals($editRev, $delRev, 'deletion should get its own revision');
        $this->assertEquals(
            DOKU_CHANGE_TYPE_DELETE,
            $changelog->getRevisionInfo($delRev)['type'],
            'current revision should be the deletion'
        );
        $this->assertEquals(
            $editRev,
            $changelog->getRelativeRevision($delRev, -1),
            'the revision before the deletion should be the last edit'
        );
    }

    /**
     * An external deletion is detected and persisted on first read as its own revision
     * with an unknown exact date, newer than the last content revision.
     * getRelativeRevision() must walk back from it to that last content revision
     * (issue #4635).
     */
    public function testRevisionBeforeExternalDeletion()
    {
        $page = 'changelog_extdeleted';
        saveWikiText($page, 'first content', 'create', false);
        $this->waitForTick(true);
        saveWikiText($page, 'second content longer', 'edit', false);
        $this->waitForTick(true);

        $editRev = (new PageChangeLog($page))->currentRevision();

        // delete the page file externally, bypassing DokuWiki
        unlink(wikiFN($page));
        clearstatcache();

        // first read detects and persists the external deletion
        $changelog = new PageChangeLog($page);
        $delRev = $changelog->currentRevision();
        $delInfo = $changelog->getRevisionInfo($delRev);

        $this->assertNotEquals($editRev, $delRev, 'external deletion should get its own revision');
        $this->assertEquals(DOKU_CHANGE_TYPE_DELETE, $delInfo['type'], 'current revision should be the deletion');
        $this->assertFalse($delInfo['timestamp'], 'external deletion has an unknown exact date');
        $this->assertEquals(
            $editRev,
            $changelog->getRelativeRevision($delRev, -1),
            'the revision before the external deletion should be the last edit'
        );
    }
}
