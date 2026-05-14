<?php

namespace dokuwiki\ChangeLog;

/**
 * Class PageChangeLog; handles changelog of a wiki page
 */
class PageChangeLog extends ChangeLog
{
    /**
     * Returns path to changelog
     *
     * @return string path to file
     */
    protected function getChangelogFilename()
    {
        return metaFN($this->id, '.changes');
    }

    /**
     * Returns path to current page/media
     *
     * @param string|int $rev empty string or revision timestamp
     * @return string path to file
     */
    protected function getFilename($rev = '')
    {
        return wikiFN($this->id, $rev);
    }

    /**
     * Returns mode
     *
     * @return string RevisionInfo::MODE_PAGE
     */
    protected function getMode()
    {
        return RevisionInfo::MODE_PAGE;
    }

    /**
     * Returns path to the global page-changelog file
     *
     * @return string path to file
     */
    protected function getGlobalChangelogFilename()
    {
        global $conf;
        return $conf['changelog'];
    }

    /**
     * Copy the externally-edited page to the attic at the synthesized revision date.
     * If the file mtime is older than the last known revision (broken chronology),
     * touch the file forward so future reads see a consistent state.
     *
     * @param array $revInfo synthesized revision info
     * @return bool true on success (or nothing to copy), false if the attic write failed
     */
    protected function saveExternalAttic(array $revInfo)
    {
        $file = $this->getFilename();
        if (!file_exists($file)) return true;

        // rescue: file mtime older than last revision — touch forward to the synthesized date
        if (empty($revInfo['timestamp'])) {
            if (!@touch($file, $revInfo['date'])) return false;
            clearstatcache(false, $file);
        }

        $atticfile = $this->getFilename($revInfo['date']);
        return io_writeWikiPage($atticfile, io_readWikiPage($file, $this->id, ''), $this->id, $revInfo['date']);
    }
}
