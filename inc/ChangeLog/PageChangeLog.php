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
     * @param string|int $rev empty string or revision timestamp
     * @return string path to file
     */
    protected function getFilename($rev = '')
    {
        return wikiFN($this->id, $rev);
    }
}
