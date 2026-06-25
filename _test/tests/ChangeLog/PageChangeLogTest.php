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

    /**
     * A current revision's file can have its modification time bumped without any content
     * change (a backup restore, a git checkout, ...). That must not be recorded as an
     * external edit: the content is compared against the last revision and, when identical,
     * the file mtime is reset to the recorded revision date instead (issue #4634).
     */
    public function testTouchedFileWithUnchangedContentIsNotExternalEdit()
    {
        $page = 'changelog_touched';
        saveWikiText($page, 'first content', 'create', false);

        $changelog = new PageChangeLog($page);
        $lastRev = $changelog->currentRevision();

        // bump the file mtime forward without changing the content
        touch(wikiFN($page), $lastRev + 1000);
        clearstatcache();

        $changelog = new PageChangeLog($page);
        $currentRev = $changelog->currentRevision();
        $currentInfo = $changelog->getRevisionInfo($currentRev);

        $this->assertEquals($lastRev, $currentRev, 'unchanged content must not create an external revision');
        $this->assertArrayNotHasKey('timestamp', $currentInfo, 'should not be a synthesized external edit');
        $this->assertCount(1, $changelog->getRevisions(-1, 200), 'no external edit entry should be added');

        clearstatcache();
        $this->assertEquals($lastRev, filemtime(wikiFN($page)), 'file mtime should be reset to the changelog date');
    }

    /**
     * A detected external edit whose date predates the most recent change already recorded
     * in the global changelog must stay out of the recent-changes feed (or it would appear
     * above newer entries with an old date), but is still recorded in the page's own
     * changelog (issue #4634).
     */
    public function testOutOfOrderExternalEditKeptOutOfGlobalChangelog()
    {
        global $conf;
        $page = 'changelog_outoforder';
        saveWikiText($page, 'first content', 'create', false);

        $changelog = new PageChangeLog($page);
        $createRev = $changelog->currentRevision();

        // external edit with different content, dated after the create but well before the
        // global changelog's last-modified time
        $globalFile = $conf['changelog'];
        $extRev = $createRev + 10;
        file_put_contents(wikiFN($page), 'externally edited content');
        touch(wikiFN($page), $extRev);
        touch($globalFile, $createRev + 100000);
        clearstatcache();

        $changelog = new PageChangeLog($page);
        $detectedRev = $changelog->currentRevision();
        $detectedInfo = $changelog->getRevisionInfo($detectedRev);

        // detected and recorded in the page's own changelog: the create plus the external edit
        $this->assertEquals($extRev, $detectedRev, 'external edit should be detected at the file mtime');
        $this->assertEquals(DOKU_CHANGE_TYPE_EDIT, $detectedInfo['type'], 'should be an external edit');
        $this->assertEquals(
            [$extRev, $createRev],
            $changelog->getRevisions(-1, 200),
            'page changelog should hold the create and the external edit'
        );

        // ...but kept out of the global recent-changes feed
        $this->assertStringNotContainsString(
            "$extRev\t",
            file_get_contents($globalFile),
            'out-of-order external edit must not be appended to the global changelog'
        );
    }

    /**
     * A genuinely current external edit (dated at or after the global changelog's last
     * recorded change) must still reach the recent-changes feed (issue #4634).
     */
    public function testCurrentExternalEditReachesGlobalChangelog()
    {
        global $conf;
        $page = 'changelog_freshext';
        saveWikiText($page, 'first content', 'create', false);

        $changelog = new PageChangeLog($page);
        $createRev = $changelog->currentRevision();

        // external edit dated after the create, so it is newer than the feed's most recent
        // change (the create just written there) and should be appended
        $extRev = $createRev + 100;
        file_put_contents(wikiFN($page), 'externally edited content');
        touch(wikiFN($page), $extRev);
        clearstatcache();

        $changelog = new PageChangeLog($page);
        $changelog->currentRevision();

        $this->assertStringContainsString(
            "$extRev\t",
            file_get_contents($conf['changelog']),
            'a current external edit should be appended to the global changelog'
        );
    }
}
