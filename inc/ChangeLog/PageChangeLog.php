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
     * Adds an entry to the changelog
     *
     * @param array $info    Revision info structure of a page
     * @param int $timestamp log line date (optional)
     * @return array revision info of added log line
     *
     * @see also addLogEntry() in inc/changelog.php file
     */
    public function addLogEntry(array $info, $timestamp = null)
    {
        global $conf;

        if (isset($timestamp)) unset($this->cache[$this->id][$info['date']]);

        // add changelog lines
        $logline = static::buildLogLine($info, $timestamp);
        io_saveFile(metaFN($this->id, '.changes'), $logline, true);
        io_saveFile($conf['changelog'], $logline, true); //global changelog cache

        // update cache
        $this->currentRevision = $info['date'];
        $info['mode'] = $this->getMode();
        $this->cache[$this->id][$this->currentRevision] = $info;
        return $info;
    }
}
