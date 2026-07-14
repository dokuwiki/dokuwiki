<?php

namespace dokuwiki\ChangeLog;

/**
 * Class MediaChangeLog; handles changelog of a media file
 */
class MediaChangeLog extends ChangeLog
{
    /**
     * Returns path to changelog
     *
     * @return string path to file
     */
    protected function getChangelogFilename()
    {
        return mediaMetaFN($this->id, '.changes');
    }

    /**
     * Returns path to current page/media
     *
     * @param string|int $rev empty string or revision timestamp
     * @return string path to file
     */
    protected function getFilename($rev = '')
    {
        return mediaFN($this->id, $rev);
    }

    /**
     * Returns mode
     *
     * @return string RevisionInfo::MODE_PAGE
     */
    protected function getMode()
    {
        return RevisionInfo::MODE_MEDIA;
    }

    /**
     * Returns path to the global media-changelog file
     *
     * @return string path to file
     */
    protected function getGlobalChangelogFilename()
    {
        global $conf;
        return $conf['media_changelog'];
    }

    /**
     * Media deliberately does not keep an archive of its current revision — only the previous
     * content is copied to the attic when a file is replaced or deleted (to save space). A
     * detected external edit is the new current revision, so it is not snapshotted either: it
     * will be archived like any other revision if and when it is later replaced. The changelog
     * entry is still recorded by the caller, and the file-mtime repair for an unreliable date
     * is handled by the base class.
     *
     * @param array $revInfo synthesized revision info (unused: nothing is archived)
     * @return bool always true
     */
    protected function saveExternalAttic(array $revInfo)
    {
        return true;
    }

    /**
     * Byte size of the last recorded revision.
     *
     * Media never archives its current revision (only the previous content is copied to the
     * attic on replace or delete), so the last revision has no attic copy and its size cannot
     * be read from disk. It is reconstructed instead as the previous revision's archived size
     * plus the size change logged for the last revision. The first revision has no previous
     * one, so its logged change is already its full size.
     *
     * @param int $recordedRev timestamp of the last recorded revision
     * @return int size in bytes (0 when it cannot be determined)
     */
    protected function lastRevisionSize($recordedRev)
    {
        $revInfo = $this->getRevisionInfo($recordedRev, false);
        $sizechange = is_array($revInfo) ? (int) $revInfo['sizechange'] : 0;

        $prev = $this->getRelativeRevision($recordedRev, -1);
        $prevSize = ($prev === false) ? 0 : io_getSizeFile($this->getFilename($prev));

        return max($prevSize + $sizechange, 0);
    }

    /**
     * Tell a real external edit from a mere mtime bump (touch, rsync --times, unzip of
     * identical bytes, ...).
     *
     * The current media revision is never archived, so there is no stored copy to compare the
     * current file against byte for byte. Its expected size is reconstructed instead (see
     * lastRevisionSize()) and compared to the current file's size: a pure mtime bump leaves the
     * size unchanged, so an equal size means the content did not change. A same-size external
     * replacement cannot be told apart this way and is (rarely) treated as unchanged.
     *
     * @param int $rev revision timestamp to compare the current file against (the last revision)
     * @return bool true if the content is considered unchanged
     */
    protected function currentContentMatchesRevision($rev)
    {
        $current = $this->getFilename();
        if (!file_exists($current)) return false;

        return filesize($current) === $this->lastRevisionSize($rev);
    }
}
