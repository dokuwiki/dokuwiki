<?php

namespace dokuwiki\plugin\struct\meta;

class Page {

    /** @var \helper_plugin_sqlite */
    protected $sqlite;

    protected $pid;
    protected $title = null;
    protected $lasteditor = null;

    public function __construct($pid) {
        /** @var \helper_plugin_struct_db $helper */
        $helper = plugin_load('helper', 'struct_db');
        $this->sqlite = $helper->getDB();
        $this->pid = $pid;
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

        $this->updateEntry('title', $title);

        $this->title = $title;
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

        $this->updateEntry('lasteditor', $lastEditor);

        $this->lasteditor = $lastEditor;
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

        $this->updateEntry('lastrev', $lastrev);

        $this->title = $lastrev;
    }

    protected function updateEntry($column, $entry) {
        if ( !is_string($column) || !in_array($column, ['title', 'lasteditor', 'lastrev'])) {
            throw new \Exception('invalid column given! ' . $column);
        }
        // only one of these will succeed
        $sql = "UPDATE OR IGNORE titles SET $column = ? WHERE pid = ?";
        $this->sqlite->query($sql, array($entry, $this->pid));

        $sql = "INSERT OR IGNORE INTO titles ($column, pid) VALUES (?,?)";
        $this->sqlite->query($sql, array($entry, $this->pid));
    }

    /**
     * @return string the page's ID
     */
    public function getPid() {
        return $this->pid;
    }
}
