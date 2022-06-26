<?php

namespace dokuwiki\ChangeLog;

/**
 * handles changelog of a wiki page
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
     * @return string path to file
     */
    protected function getFilename()
    {
        return wikiFN($this->id);
    }
}
