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
     * @param string $rev page revision, empty string for current
     * @return string path to file
     */
    protected function getFilename($rev = '')
    {
        return wikiFN($this->id, $rev);
    }

    /**
     * @return array|false
     */
    protected function buildExternalEditLogline()
    {
        global $lang;
        $externaleditRevinfo = false;

        //in attic no revision of current existing wiki page, so external edit occurred
        $fileLastMod = $this->getFilename();//wikiFN($this->id);
        $lastMod = @filemtime($fileLastMod); // from wiki page, suppresses warning in case the file not exists
        $lastRev = $this->getRevisions(-1, 1); // from changelog
        $lastRev = (int)(empty($lastRev) ? 0 : $lastRev[0]);
        if (!file_exists($this->getFilename($lastMod)) && file_exists($fileLastMod) && $lastRev < $lastMod) {
            $fileLastRev = $this->getFilename($lastRev);
            $revinfo = $this->getRevisionInfo($lastRev);
            $type = DOKU_CHANGE_TYPE_EDIT;
            if (empty($lastRev) || !file_exists($fileLastRev) || $revinfo['type'] == DOKU_CHANGE_TYPE_DELETE) {
                $filesize_old = 0;
                $type = DOKU_CHANGE_TYPE_CREATE;
            } else {
                $filesize_old = io_getSizeFile($fileLastRev);
            }
            $filesize_new = filesize($fileLastMod);
            $sizechange = $filesize_new - $filesize_old;

            $externaleditRevinfo = [
                'date' => $lastMod,
                'ip' => '127.0.0.1',
                'type' => $type,
                'id' => $this->id,
                'user' => '',
                'sum' => ($type == DOKU_CHANGE_TYPE_CREATE ? $lang['created'] . ' - ' : '') . $lang['external_edit'],
                'extra' => '',
                'sizechange' => $sizechange
            ];
        }

        $revinfo = $this->getRevisionInfo($lastRev);
        //deleted wiki page, but not registered in changelog
        if (!file_exists($fileLastMod) // there is no current page=>true
            && !empty($lastRev) && $revinfo['type'] !== DOKU_CHANGE_TYPE_DELETE) {
            $fileLastRev = $this->getFilename($lastRev);
            $externaleditRevinfo = [
                'date' => 9999999999, //unknown deletion date, always higher as latest rev
                'ip' => '127.0.0.1',
                'type' => DOKU_CHANGE_TYPE_DELETE,
                'id' => $this->id,
                'user' => '',
                'sum' => $lang['deleted'] . ' - ' . $lang['external_edit'],
                'extra' => '',
                'sizechange' => -io_getSizeFile($fileLastRev)
            ];
        }
        return $externaleditRevinfo;
    }
}
