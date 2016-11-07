<?php

namespace dokuwiki\plugin\struct\meta;

class Title {

    /** @var \helper_plugin_sqlite */
    protected $sqlite;

    protected $pid;
    protected $title = null;

    public function __construct($pid) {
        /** @var \helper_plugin_struct_db $helper */
        $helper = plugin_load('helper', 'struct_db');
        $this->sqlite = $helper->getDB();
        $this->pid = $pid;
    }

    /**
     * Load the title from the database
     *
     * @return null|string the current title, null if not exists
     */
    protected function loadTitle() {
        $sql = "SELECT title FROM titles WHERE pid = ?";
        $res = $this->sqlite->query($sql, $this->pid);
        $title = $this->sqlite->res2single($res);
        $this->sqlite->res_close($res);

        if($title === false) return null;
        return $title;
    }

    /**
     * Sets a new title in the database;
     *
     * @param string|null $title set null to derive from PID
     */
    public function setTitle($title) {
        if($title === null) {
            $title = noNS($this->pid);
        }

        // only one of these will succeed
        $sql = "UPDATE OR IGNORE titles SET title = ? WHERE pid = ?";
        $this->sqlite->query($sql, array($title, $this->pid));

        $sql = "INSERT OR IGNORE INTO titles (title, pid) VALUES (?,?)";
        $this->sqlite->query($sql, array($title, $this->pid));

        $this->title = $title;
    }

    /**
     * @return string the page's title
     */
    public function getTitle() {
        // try to load from database
        if($this->title === null) {
            $this->title = $this->loadTitle();
        }
        // still none? set to pid
        if($this->title === null) {
            $this->setTitle(null);
        }

        return $this->title;
    }

    /**
     * @return string the page's ID
     */
    public function getPid() {
        return $this->pid;
    }
}
