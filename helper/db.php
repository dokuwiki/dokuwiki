<?php
/**
 * DokuWiki Plugin struct (Helper Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Andreas Gohr, Michael Große <dokuwiki@cosmocode.de>
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

class helper_plugin_struct_db extends DokuWiki_Plugin {
    /** @var helper_plugin_sqlite */
    protected $sqlite;

    public function __construct() {
        /** @var helper_plugin_sqlite $sqlite */
        $this->sqlite = plugin_load('helper', 'sqlite');
        if(!$this->sqlite) {
            msg('The struct plugin requires the sqlite plugin. Please install it', -1);
            return;
        }

        if($this->sqlite->getAdapter()->getName() != DOKU_EXT_PDO) {
            msg('The struct plugin requires sqlite3 you\'re still using sqlite2');
            $this->sqlite = null;
            return;
        }
        $this->sqlite->getAdapter()->setUseNativeAlter(true);

        // initialize the database connection
        if(!$this->sqlite->init('struct', DOKU_PLUGIN . 'struct/db/')) {
            return;
        }
    }

    /**
     * @return helper_plugin_sqlite|null
     */
    public function getDB() {
        return $this->sqlite;
    }

}

// vim:ts=4:sw=4:et:
