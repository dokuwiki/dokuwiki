<?php

namespace dokuwiki\test\ChangeLog;

use dokuwiki\ChangeLog\MediaChangeLog;

/**
 * Tests for dokuwiki\ChangeLog\MediaChangeLog.
 *
 * Media files differ from pages in one way that matters to external-change detection: media
 * never archives its current revision (only the previous content is copied to the attic on
 * replace or delete). So the attic copy of the current revision is always missing, and the
 * base-class logic that reads it - the unchanged-content guard and the old-size lookup for
 * the size change - has to be handled specially for media.
 */
class MediaChangeLogTest extends \DokuWikiTest
{
    /**
     * Write a media file with the given content and mtime and record a matching changelog entry,
     * leaving file mtime and changelog in agreement (as a real DokuWiki upload does).
     */
    protected function seedMedia($image, $content, $date, $type = DOKU_CHANGE_TYPE_CREATE, $sizechange = null)
    {
        io_saveFile(mediaFN($image), $content);
        touch(mediaFN($image), $date);
        clearstatcache();
        addMediaLogEntry(
            $date,
            $image,
            $type,
            $type === DOKU_CHANGE_TYPE_CREATE ? 'created' : 'edited',
            '',
            null,
            $sizechange === null ? strlen($content) : $sizechange
        );
    }

    /**
     * A media file whose mtime is bumped without any content change (a touch, an rsync --times,
     * an unzip of identical bytes) must not be recorded as an external edit. The current media
     * revision is never archived, so there is no copy to compare against; the recorded size is
     * used instead, and an unchanged size means the content did not change: the mtime is reset
     * to the recorded revision date and no changelog entry is added.
     */
    public function testTouchedMediaWithUnchangedContentIsNotExternalEdit()
    {
        $image = 'changelog:touched.png';
        $lastRev = time() - 1000;
        $this->seedMedia($image, str_repeat('x', 100), $lastRev);

        // bump the file mtime forward without changing the content
        touch(mediaFN($image), $lastRev + 500);
        clearstatcache();

        $changelog = new MediaChangeLog($image);
        $currentRev = $changelog->currentRevision();
        $currentInfo = $changelog->getRevisionInfo($currentRev);

        $this->assertEquals($lastRev, $currentRev, 'unchanged content must not create an external revision');
        $this->assertArrayNotHasKey('timestamp', $currentInfo, 'should not be a synthesized external edit');
        $this->assertCount(1, $changelog->getRevisions(-1, 200), 'no external edit entry should be added');
        $this->assertFileDoesNotExist(mediaFN($image, $lastRev + 500), 'no attic snapshot should be written');

        clearstatcache();
        $this->assertEquals($lastRev, filemtime(mediaFN($image)), 'file mtime should be reset to the changelog date');
    }

    /**
     * A media file genuinely replaced outside DokuWiki (different content, newer mtime) is an
     * external edit. Its size change must be computed against the recorded size of the previous
     * revision, not against the missing attic copy of the current revision (which would report a
     * zero old size and therefore the whole new file as the change). The new content is not
     * snapshotted to the attic: media never archives its current revision.
     */
    public function testExternallyReplacedMediaRecordsCorrectSizeChange()
    {
        $image = 'changelog:replaced.png';
        $lastRev = time() - 1000;
        $this->seedMedia($image, str_repeat('a', 100), $lastRev);

        // replace the file externally with a differently-sized one, newer mtime
        $extRev = $lastRev + 500;
        io_saveFile(mediaFN($image), str_repeat('b', 250));
        touch(mediaFN($image), $extRev);
        clearstatcache();

        $changelog = new MediaChangeLog($image);
        $currentRev = $changelog->currentRevision();
        $currentInfo = $changelog->getRevisionInfo($currentRev);

        $this->assertEquals($extRev, $currentRev, 'external edit detected at the file mtime');
        $this->assertEquals(DOKU_CHANGE_TYPE_EDIT, $currentInfo['type'], 'should be an external edit');
        $this->assertEquals(
            250 - 100,
            $currentInfo['sizechange'],
            'the old size must come from the recorded revision, not zero against the missing attic'
        );
        $this->assertFileDoesNotExist(
            mediaFN($image, $extRev),
            'the current revision is not archived, so the external edit is not snapshotted either'
        );
    }

    /**
     * With more than one recorded revision the old size is reconstructed from the previous
     * revision's real attic copy plus the size change logged for the current revision, so a
     * multi-revision media file still yields the correct external-edit size change.
     */
    public function testExternalEditReconstructsOldSizeFromPreviousAttic()
    {
        $image = 'changelog:multi.png';
        $rev1 = time() - 2000;
        $this->seedMedia($image, str_repeat('a', 100), $rev1);

        // simulate a DokuWiki replace: archive the current revision, write new content, log the edit
        $rev2 = $rev1 + 500;
        io_makeFileDir(mediaFN($image, $rev1));
        copy(mediaFN($image), mediaFN($image, $rev1)); // what media_saveOldRevision() does
        $this->seedMedia($image, str_repeat('b', 300), $rev2, DOKU_CHANGE_TYPE_EDIT, 300 - 100);

        // now replace the current (unarchived) revision externally
        $rev3 = $rev2 + 500;
        io_saveFile(mediaFN($image), str_repeat('c', 120));
        touch(mediaFN($image), $rev3);
        clearstatcache();

        $changelog = new MediaChangeLog($image);
        $currentRev = $changelog->currentRevision();
        $currentInfo = $changelog->getRevisionInfo($currentRev);

        $this->assertEquals($rev3, $currentRev);
        $this->assertEquals(DOKU_CHANGE_TYPE_EDIT, $currentInfo['type']);
        $this->assertEquals(
            120 - 300,
            $currentInfo['sizechange'],
            'old size reconstructed from the previous attic (300) plus the logged change'
        );
    }

    /**
     * An external media edit whose mtime is older than the last revision cannot trust that
     * mtime as its date, so it is dated just after the last revision. The current file's mtime
     * is then repaired forward to that date (media does not archive, but the base class still
     * fixes the mtime) so the same change is not re-detected on the next read.
     */
    public function testExternalEditWithOlderMtimeIsRepairedAndStable()
    {
        $image = 'changelog:oldmtime.png';
        $lastRev = time() - 1000;
        $this->seedMedia($image, str_repeat('a', 100), $lastRev);

        // replace externally with different content but an mtime older than the last revision
        io_saveFile(mediaFN($image), str_repeat('b', 250));
        touch(mediaFN($image), $lastRev - 100);
        clearstatcache();

        $this->expectLogMessage('current file modification time is older than last revision date');

        $changelog = new MediaChangeLog($image);
        $currentRev = $changelog->currentRevision();
        $currentInfo = $changelog->getRevisionInfo($currentRev);

        $this->assertEquals(DOKU_CHANGE_TYPE_EDIT, $currentInfo['type']);
        $this->assertFalse($currentInfo['timestamp'], 'an older-than-last mtime cannot be trusted as the date');
        $this->assertEquals($lastRev + 1, $currentRev, 'the edit is dated just after the last revision');

        // the file mtime is repaired forward, so a fresh read does not re-detect the change
        clearstatcache();
        $this->assertEquals($lastRev + 1, filemtime(mediaFN($image)), 'file mtime repaired to the synthesized date');
        $fresh = new MediaChangeLog($image);
        $this->assertEquals($lastRev + 1, $fresh->currentRevision(), 'no re-detection on the next read');
        $this->assertCount(2, $fresh->getRevisions(-1, 200), 'exactly one external edit was recorded');
    }
}
