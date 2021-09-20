<?php

namespace dokuwiki\ChangeLog;

/**
 * handles changelog of a media file
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
}
