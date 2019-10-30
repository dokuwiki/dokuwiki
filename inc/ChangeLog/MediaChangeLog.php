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
     * @return string path to file
     */
    protected function getFilename()
    {
        return mediaFN($this->id);
    }
}
