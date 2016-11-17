<?php

namespace dokuwiki\plugin\struct\meta;

class Page {

    /** @var \helper_plugin_sqlite */
    protected $sqlite;

    protected $pid;
    protected $title = null;
    protected $lasteditor = null;
    protected $lastrev = null;

    protected $saveNeeded = false;

    public function __construct($pid) {
        /** @var \helper_plugin_struct_db $helper */
        $helper = plugin_load('helper', 'struct_db');
        $this->sqlite = $helper->getDB();
        $this->pid = $pid;
    }

    /**
     * If data was explicitly set, then save it to the database if that hasn't happened yet.
     */
    public function __destruct() {
        if ($this->saveNeeded) {
            $this->savePageData();
        }
    }

    /**
     * Save title, last editor and revision timestamp to database
     */
    public function savePageData() {
        $sql = "REPLACE INTO titles (pid, title, lasteditor, lastrev) VALUES (?,?,?,?)";
        $this->sqlite->query($sql, array($this->pid, $this->title, $this->lasteditor, $this->lastrev));
        $this->saveNeeded = false;
    }

    /**
     * Sets a new title
     *
     * @param string|null $title set null to derive from PID
     */
    public function setTitle($title) {
        if($title === null) {
            $title = noNS($this->pid);
        }

        $this->title = $title;
        $this->saveNeeded = true;
    }

    /**
     * Sets the last editor
     *
     * @param string|null $lastEditor
     */
    public function setLastEditor($lastEditor) {
        if($lastEditor === null) {
            $lastEditor = '';
        }

        $this->lasteditor = $lastEditor;
        $this->saveNeeded = true;
    }

    /**
     * Sets the revision timestamp
     *
     * @param int|null $lastrev
     */
    public function setLastRevision($lastrev) {
        if($lastrev === null) {
            $lastrev = 0;
        }

        $this->lastrev = $lastrev;
        $this->saveNeeded = true;
    }

    /**
     * @return string the page's ID
     */
    public function getPid() {
        return $this->pid;
    }
}
