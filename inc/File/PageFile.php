<?php

namespace dokuwiki\File;

use dokuwiki\Cache\CacheInstructions;
use dokuwiki\ChangeLog\PageChangeLog;
use dokuwiki\Extension\Event;
use dokuwiki\Input\Input;
use dokuwiki\Logger;
use RuntimeException;

/**
 * Class PageFile : handles wiki text file and its change management for specific page
 */
class PageFile
{
    protected $id;

    /* @var PageChangeLog $changelog */
    public $changelog;

    /* @var array $data  initial data when event COMMON_WIKIPAGE_SAVE triggered */
    protected $data;

    /**
     * PageFile constructor.
     *
     * @param string $id
     */
    public function __construct($id)
    {
        $this->id = $id;
        $this->changelog = new PageChangeLog($this->id);
    }

    /** @return string */
    public function getId()
    {
        return $this->id;
    }

    /** @return string */
    public function getPath($rev = '')
    {
        return wikiFN($this->id, $rev);
    }

    /**
     * Get raw WikiText of the page, considering change type at revision date
     * similar to function rawWiki($id, $rev = '')
     *
     * @param int|false $rev  timestamp when a revision of wikitext is desired
     * @return string
     */
    public function rawWikiText($rev = null)
    {
        if ($rev !== null) {
            $revInfo = $rev ? $this->changelog->getRevisionInfo($rev) : false;
            return (!$revInfo || $revInfo['type'] == DOKU_CHANGE_TYPE_DELETE)
                ? '' // attic stores complete last page version for a deleted page
                : io_readWikiPage($this->getPath($rev), $this->id, $rev); // retrieve from attic
        } else {
            return io_readWikiPage($this->getPath(), $this->id, '');
        }
    }

    /**
     * Saves a wikitext by calling io_writeWikiPage.
     * Also directs changelog and attic updates.
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     * @author Ben Coburn <btcoburn@silicodon.net>
     *
     * @param string $text     wikitext being saved
     * @param string $summary  summary of text update
     * @param bool   $minor    mark this saved version as minor update
     * @return array|void data of event COMMON_WIKIPAGE_SAVE
     */
    public function saveWikiText($text, $summary, $minor = false)
    {
        /* Note to developers:
           This code is subtle and delicate. Test the behavior of
           the attic and changelog with dokuwiki and external edits
           after any changes. External edits change the wiki page
           directly without using php or dokuwiki.
         */
        global $conf;
        global $lang;
        global $REV;
        /* @var Input $INPUT */
        global $INPUT;

        // prevent recursive call
        if (isset($this->data)) return;

        $pagefile = $this->getPath();
        $currentRevision = @filemtime($pagefile);       // int or false
        $currentContent = $this->rawWikiText();
        $currentSize = file_exists($pagefile) ? filesize($pagefile) : 0;

        // prepare data for event COMMON_WIKIPAGE_SAVE
        $data = [
            'id'             => $this->id,// should not be altered by any handlers
            'file'           => $pagefile,// same above
            'changeType'     => null,// set prior to event, and confirm later
            'revertFrom'     => $REV,
            'oldRevision'    => $currentRevision,
            'oldContent'     => $currentContent,
            'newRevision'    => 0,// only available in the after hook
            'newContent'     => $text,
            'summary'        => $summary,
            'contentChanged' => ($text != $currentContent),// confirm later
            'changeInfo'     => '',// automatically determined by revertFrom
            'sizechange'     => strlen($text) - strlen($currentContent),
        ];

        // determine tentatively change type and relevant elements of event data
        if ($data['revertFrom']) {
            // new text may differ from exact revert revision
            $data['changeType'] = DOKU_CHANGE_TYPE_REVERT;
            $data['changeInfo'] = $REV;
        } elseif (trim($data['newContent']) == '') {
            // empty or whitespace only content deletes
            $data['changeType'] = DOKU_CHANGE_TYPE_DELETE;
        } elseif (!file_exists($pagefile)) {
            $data['changeType'] = DOKU_CHANGE_TYPE_CREATE;
        } else {
            // minor edits allowable only for logged in users
            $is_minor_change = ($minor && $conf['useacl'] && $INPUT->server->str('REMOTE_USER'));
            $data['changeType'] = $is_minor_change
                ? DOKU_CHANGE_TYPE_MINOR_EDIT
                : DOKU_CHANGE_TYPE_EDIT;
        }

        $this->data = $data;
        $data['page'] = $this; // allow event handlers to use this class methods

        $event = new Event('COMMON_WIKIPAGE_SAVE', $data);
        if (!$event->advise_before()) return;

        // if the content has not been changed, no save happens (plugins may override this)
        if (!$data['contentChanged']) return;

        // Check whether the pagefile has modified during $event->advise_before()
        clearstatcache();
        $fileRev = @filemtime($pagefile);
        if ($fileRev === $currentRevision) {
            // pagefile has not touched by plugin's event handler
            // add a potential external edit entry to changelog and store it into attic
            $this->detectExternalEdit();
            $filesize_old = $currentSize;
        } else {
            // pagefile has modified by plugin's event handler, confirm sizechange
            $filesize_old = (
                $data['changeType'] == DOKU_CHANGE_TYPE_CREATE || (
                $data['changeType'] == DOKU_CHANGE_TYPE_REVERT && !file_exists($pagefile))
            ) ? 0 : filesize($pagefile);
        }

        // make change to the current file
        if ($data['changeType'] == DOKU_CHANGE_TYPE_DELETE) {
            // nothing to do when the file has already deleted
            if (!file_exists($pagefile)) return;
            // autoset summary on deletion
            if (blank($data['summary'])) {
                $data['summary'] = $lang['deleted'];
            }
            // send "update" event with empty data, so plugins can react to page deletion
            $ioData = [[$pagefile, '', false], getNS($this->id), noNS($this->id), false];
            Event::createAndTrigger('IO_WIKIPAGE_WRITE', $ioData);
            // pre-save deleted revision
            @touch($pagefile);
            clearstatcache();
            $data['newRevision'] = $this->saveOldRevision();
            // remove empty file
            @unlink($pagefile);
            $filesize_new = 0;
            // don't remove old meta info as it should be saved, plugins can use
            // IO_WIKIPAGE_WRITE for removing their metadata...
            // purge non-persistant meta data
            p_purge_metadata($this->id);
            // remove empty namespaces
            io_sweepNS($this->id, 'datadir');
            io_sweepNS($this->id, 'mediadir');
        } else {
            // save file (namespace dir is created in io_writeWikiPage)
            io_writeWikiPage($pagefile, $data['newContent'], $this->id);
            // pre-save the revision, to keep the attic in sync
            $data['newRevision'] = $this->saveOldRevision();
            $filesize_new = filesize($pagefile);
        }
        $data['sizechange'] = $filesize_new - $filesize_old;

        $event->advise_after();

        unset($data['page']);

        // adds an entry to the changelog and saves the metadata for the page
        $logEntry = $this->changelog->addLogEntry([
            'date'       => $data['newRevision'],
            'ip'         => clientIP(true),
            'type'       => $data['changeType'],
            'id'         => $this->id,
            'user'       => $INPUT->server->str('REMOTE_USER'),
            'sum'        => $data['summary'],
            'extra'      => $data['changeInfo'],
            'sizechange' => $data['sizechange'],
        ]);
        // update metadata
        $this->updateMetadata($logEntry);

        // update the purgefile (timestamp of the last time anything within the wiki was changed)
        io_saveFile($conf['cachedir'] . '/purgefile', time());

        return $data;
    }

    /**
     * Checks if the current page version is newer than the last entry in the page's changelog.
     * If so, we assume it has been an external edit and we create an attic copy and add a proper
     * changelog line.
     *
     * This check is only executed when the page is about to be saved again from the wiki,
     * triggered in @see saveWikiText()
     */
    public function detectExternalEdit()
    {
        $revInfo = $this->changelog->getCurrentRevisionInfo();

        // only interested in external revision
        if (empty($revInfo) || !array_key_exists('timestamp', $revInfo)) return;

        if ($revInfo['type'] != DOKU_CHANGE_TYPE_DELETE && !$revInfo['timestamp']) {
            // file is older than last revision, that is erroneous/incorrect occurence.
            // try to change file modification time
            $fileLastMod = $this->getPath();
            $wrong_timestamp = filemtime($fileLastMod);
            if (touch($fileLastMod, $revInfo['date'])) {
                clearstatcache();
                $msg = "PageFile($this->id)::detectExternalEdit(): timestamp successfully modified";
                $details = '(' . $wrong_timestamp . ' -> ' . $revInfo['date'] . ')';
                Logger::error($msg, $details, $fileLastMod);
            } else {
                // runtime error
                $msg = "PageFile($this->id)::detectExternalEdit(): page file should be newer than last revision "
                      . '(' . filemtime($fileLastMod) . ' < ' . $this->changelog->lastRevision() . ')';
                throw new RuntimeException($msg);
            }
        }

        // keep at least 1 sec before new page save
        if ($revInfo['date'] == time()) sleep(1); // wait a tick

        // store externally edited file to the attic folder
        $this->saveOldRevision();
        // add a changelog entry for externally edited file
        $this->changelog->addLogEntry($revInfo);
        // remove soon to be stale instructions
        $cache = new CacheInstructions($this->id, $this->getPath());
        $cache->removeCache();
    }

    /**
     * Moves the current version to the attic and returns its revision date
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     *
     * @return int|string revision timestamp
     */
    public function saveOldRevision()
    {
        $oldfile = $this->getPath();
        if (!file_exists($oldfile)) return '';
        $date = filemtime($oldfile);
        $newfile = $this->getPath($date);
        io_writeWikiPage($newfile, $this->rawWikiText(), $this->id, $date);
        return $date;
    }

    /**
     * Update metadata of changed page
     *
     * @param array $logEntry  changelog entry
     */
    public function updateMetadata(array $logEntry)
    {
        global $INFO;

        ['date' => $date, 'type' => $changeType, 'user' => $user, ] = $logEntry;

        $wasRemoved   = ($changeType === DOKU_CHANGE_TYPE_DELETE);
        $wasCreated   = ($changeType === DOKU_CHANGE_TYPE_CREATE);
        $wasReverted  = ($changeType === DOKU_CHANGE_TYPE_REVERT);
        $wasMinorEdit = ($changeType === DOKU_CHANGE_TYPE_MINOR_EDIT);

        $createdDate = @filectime($this->getPath());

        if ($wasRemoved) return;

        $oldmeta = p_read_metadata($this->id)['persistent'];
        $meta    = [];

        if (
            $wasCreated &&
            (empty($oldmeta['date']['created']) || $oldmeta['date']['created'] === $createdDate)
        ) {
            // newly created
            $meta['date']['created'] = $createdDate;
            if ($user) {
                $meta['creator'] = $INFO['userinfo']['name'] ?? null;
                $meta['user']    = $user;
            }
        } elseif (($wasCreated || $wasReverted) && !empty($oldmeta['date']['created'])) {
            // re-created / restored
            $meta['date']['created']  = $oldmeta['date']['created'];
            $meta['date']['modified'] = $createdDate; // use the files ctime here
            $meta['creator'] = $oldmeta['creator'] ?? null;
            if ($user) {
                $meta['contributor'][$user] = $INFO['userinfo']['name'] ?? null;
            }
        } elseif (!$wasMinorEdit) {   // non-minor modification
            $meta['date']['modified'] = $date;
            if ($user) {
                $meta['contributor'][$user] = $INFO['userinfo']['name'] ?? null;
            }
        }
        $meta['last_change'] = $logEntry;
        p_set_metadata($this->id, $meta);
    }
}
