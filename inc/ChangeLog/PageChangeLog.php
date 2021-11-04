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



    /**
     * Adds an entry to the changelog
     *
     * @param array $info    Revision info structure of a page
     * @param int $timestamp logline date (optional)
     * @return array added logline as revision info
     *
     * @see also addLogEntry() in inc/changelog.php file
     */
    public function addLogEntry(array $info, $timestamp = null)
    {
        global $conf;

        $strip = ["\t", "\n"];
        $revInfo = array(
            'date' => $timestamp ?? $info['date'],
            'ip'   => $info['ip'],
            'type' => str_replace($strip, '', $info['type']),
            'id'   => $this->id,
            'user' => $info['user'],
            'sum'  => \dokuwiki\Utf8\PhpString::substr(str_replace($strip, '', $info['sum']), 0, 255),
            'extra' => str_replace($strip, '', $info['extra']),
            'sizechange' => $info['sizechange'],
        );

        // add changelog lines
        $logline = implode("\t", $revInfo) ."\n";
        io_saveFile(metaFN($this->id,'.changes'), $logline, $append = true);
        io_saveFile($conf['changelog'], $logline, $append = true); //global changelog cache

        // update cache
        if (isset($timestamp)) unset($this->cache[$this->id][$info['date']]);
        $this->currentRevision = $revInfo['date'];
        $this->cache[$this->id][$this->currentRevision] = $revInfo;
        return $revInfo;
    }

}
