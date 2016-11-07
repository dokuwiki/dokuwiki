<?php
/**
 * DokuWiki Plugin struct (Helper Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Andreas Gohr, Michael Große <dokuwiki@cosmocode.de>
 */

// must be run within Dokuwiki
use dokuwiki\plugin\struct\meta\StructException;

if(!defined('DOKU_INC')) die();

class helper_plugin_struct_db extends DokuWiki_Plugin {
    /** @var helper_plugin_sqlite */
    protected $sqlite;

    /**
     * helper_plugin_struct_db constructor.
     */
    public function __construct() {
        $this->init();
    }

    /**
     * Initialize the database
     *
     * @throws Exception
     */
    protected function init() {
        /** @var helper_plugin_sqlite $sqlite */
        $this->sqlite = plugin_load('helper', 'sqlite');
        if(!$this->sqlite) {
            if(defined('DOKU_UNITTEST')) throw new \Exception('Couldn\'t load sqlite.');
            return;
        }

        if($this->sqlite->getAdapter()->getName() != DOKU_EXT_PDO) {
            if(defined('DOKU_UNITTEST')) throw new \Exception('Couldn\'t load PDO sqlite.');
            $this->sqlite = null;
            return;
        }
        $this->sqlite->getAdapter()->setUseNativeAlter(true);

        // initialize the database connection
        if(!$this->sqlite->init('struct', DOKU_PLUGIN . 'struct/db/')) {
            if(defined('DOKU_UNITTEST')) throw new \Exception('Couldn\'t init sqlite.');
            $this->sqlite = null;
            return;
        }

        // register our JSON function with variable parameters
        // todo this might be useful to be moved into the sqlite plugin
        $this->sqlite->create_function('STRUCT_JSON', array($this, 'STRUCT_JSON'), -1);
    }

    /**
     * @param bool $throw throw an Exception when sqlite not available?
     * @return helper_plugin_sqlite|null
     */
    public function getDB($throw=true) {
        global $conf;
        $len = strlen($conf['metadir']);
        if ($this->sqlite && $conf['metadir'] != substr($this->sqlite->getAdapter()->getDbFile(),0,$len)) {
            $this->init();
        }
        if(!$this->sqlite && $throw) {
            throw new StructException('no sqlite');
        }
        return $this->sqlite;
    }

    /**
     * Completely remove the database and reinitialize it
     *
     * You do not want to call this except for testing!
     */
    public function resetDB() {
        if(!$this->sqlite) return;
        $file = $this->sqlite->getAdapter()->getDbFile();
        if(!$file) return;
        unlink($file);
        clearstatcache(true, $file);
        $this->init();
    }

    /**
     * Encodes all given arguments into a JSON encoded array
     *
     * @param string ...
     * @return string
     */
    public function STRUCT_JSON() {
        $args = func_get_args();
        return json_encode($args);
    }
}

// vim:ts=4:sw=4:et:
