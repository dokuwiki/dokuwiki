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
     * Adds an entry to the changelog
     *
     * @param array $info    Revision info structure of a media file
     * @param int $timestamp log line date (optional)
     * @return array revision info of added log line
     *
     * @see also addMediaLogEntry() in inc/changelog.php file
     */
    public function addLogEntry(array $info, $timestamp = null)
    {
        global $conf;

        if (isset($timestamp)) unset($this->cache[$this->id][$info['date']]);

        // add changelog lines
        $logline = static::buildLogLine($info, $timestamp);
        io_saveFile(mediaMetaFN($this->id, '.changes'), $logline, $append = true);
        io_saveFile($conf['media_changelog'], $logline, $append = true); //global changelog cache

        // update cache
        $this->currentRevision = $info['date'];
        $info['mode'] = $this->getMode();
        $this->cache[$this->id][$this->currentRevision] = $info;
        return $info;
    }
}
