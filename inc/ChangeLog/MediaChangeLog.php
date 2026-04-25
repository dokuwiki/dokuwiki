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
     * Copy the externally-modified media file to the attic at the synthesized revision date.
     * If the file mtime is older than the last known revision (broken chronology),
     * touch the file forward so future reads see a consistent state.
     *
     * @param array $revInfo synthesized revision info
     * @return bool true on success (or nothing to copy), false if the attic copy failed
     */
    protected function saveExternalAttic(array $revInfo)
    {
        global $conf;

        $file = $this->getFilename();
        if (!file_exists($file)) return true;

        // rescue: file mtime older than last revision — touch forward to the synthesized date
        if (empty($revInfo['timestamp'])) {
            if (!@touch($file, $revInfo['date'])) return false;
            clearstatcache(false, $file);
        }

        $atticfile = $this->getFilename($revInfo['date']);
        io_makeFileDir($atticfile);
        if (!@copy($file, $atticfile)) return false;
        if (!empty($conf['fmode'])) @chmod($atticfile, $conf['fmode']);
        return true;
    }
}
