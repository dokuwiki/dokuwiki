<?php
/**
 * Generic class to handle caching
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Chris Smith <chris@jalakai.co.uk>
 */

if(!defined('DOKU_INC')) die('meh.');

/**
 * Generic handling of caching
 */
class cache {
    public $key = '';          // primary identifier for this item
    public $ext = '';          // file ext for cache data, secondary identifier for this item
    public $cache = '';        // cache file name
    public $depends = array(); // array containing cache dependency information,
                               //   used by _useCache to determine cache validity

    var $_event = '';       // event to be triggered during useCache
    var $_time;
    var $_nocache = false;  // if set to true, cache will not be used or stored

    /**
     * @param string $key primary identifier
     * @param string $ext file extension
     */
    public function __construct($key,$ext) {
        $this->key = $key;
        $this->ext = $ext;
        $this->cache = getCacheName($key,$ext);
    }

    /**
     * public method to determine whether the cache can be used
     *
     * to assist in centralisation of event triggering and calculation of cache statistics,
     * don't override this function override _useCache()
     *
     * @param  array   $depends   array of cache dependencies, support dependecies:
     *                            'age'   => max age of the cache in seconds
     *                            'files' => cache must be younger than mtime of each file
     *                                       (nb. dependency passes if file doesn't exist)
     *
     * @return bool    true if cache can be used, false otherwise
     */
    public function useCache($depends=array()) {
        $this->depends = $depends;
        $this->_addDependencies();

        if ($this->_event) {
            return $this->_stats(trigger_event($this->_event, $this, array($this,'_useCache')));
        } else {
            return $this->_stats($this->_useCache());
        }
    }

    /**
     * private method containing cache use decision logic
     *
     * this function processes the following keys in the depends array
     *   purge - force a purge on any non empty value
     *   age   - expire cache if older than age (seconds)
     *   files - expire cache if any file in this array was updated more recently than the cache
     *
     * Note that this function needs to be public as it is used as callback for the event handler
     *
     * can be overridden
     *
     * @return bool               see useCache()
     */
    public function _useCache() {

        if ($this->_nocache) return false;                              // caching turned off
        if (!empty($this->depends['purge'])) return false;              // purge requested?
        if (!($this->_time = @filemtime($this->cache))) return false;   // cache exists?

        // cache too old?
        if (!empty($this->depends['age']) && ((time() - $this->_time) > $this->depends['age'])) return false;

        if (!empty($this->depends['files'])) {
            foreach ($this->depends['files'] as $file) {
                if ($this->_time <= @filemtime($file)) return false;         // cache older than files it depends on?
            }
        }

        return true;
    }

    /**
     * add dependencies to the depends array
     *
     * this method should only add dependencies,
     * it should not remove any existing dependencies and
     * it should only overwrite a dependency when the new value is more stringent than the old
     */
    protected function _addDependencies() {
        global $INPUT;
        if ($INPUT->has('purge')) $this->depends['purge'] = true;   // purge requested
    }

    /**
     * retrieve the cached data
     *
     * @param   bool   $clean   true to clean line endings, false to leave line endings alone
     * @return  string          cache contents
     */
    public function retrieveCache($clean=true) {
        return io_readFile($this->cache, $clean);
    }

    /**
     * cache $data
     *
     * @param   string $data   the data to be cached
     * @return  bool           true on success, false otherwise
     */
    public function storeCache($data) {
        if ($this->_nocache) return false;

        return io_savefile($this->cache, $data);
    }

    /**
     * remove any cached data associated with this cache instance
     */
    public function removeCache() {
        @unlink($this->cache);
    }

    /**
     * Record cache hits statistics.
     * (Only when debugging allowed, to reduce overhead.)
     *
     * @param    bool   $success   result of this cache use attempt
     * @return   bool              pass-thru $success value
     */
    protected function _stats($success) {
        global $conf;
        static $stats = null;
        static $file;

        if (!$conf['allowdebug']) { return $success; }

        if (is_null($stats)) {
            $file = $conf['cachedir'].'/cache_stats.txt';
            $lines = explode("\n",io_readFile($file));

            foreach ($lines as $line) {
                $i = strpos($line,',');
                $stats[substr($line,0,$i)] = $line;
            }
        }

        if (isset($stats[$this->ext])) {
            list($ext,$count,$hits) = explode(',',$stats[$this->ext]);
        } else {
            $ext = $this->ext;
            $count = 0;
            $hits = 0;
        }

        $count++;
        if ($success) $hits++;
        $stats[$this->ext] = "$ext,$count,$hits";

        io_saveFile($file,join("\n",$stats));

        return $success;
    }
}

/**
 * Parser caching
 */
class cache_parser extends cache {

    public $file = '';       // source file for cache
    public $mode = '';       // input mode (represents the processing the input file will undergo)
    public $page = '';

    var $_event = 'PARSER_CACHE_USE';

    /**
     *
     * @param string $id page id
     * @param string $file source file for cache
     * @param string $mode input mode
     */
    public function __construct($id, $file, $mode) {
        if ($id) $this->page = $id;
        $this->file = $file;
        $this->mode = $mode;

        parent::__construct($file.$_SERVER['HTTP_HOST'].$_SERVER['SERVER_PORT'],'.'.$mode);
    }

    /**
     * method contains cache use decision logic
     *
     * @return bool               see useCache()
     */
    public function _useCache() {

        if (!file_exists($this->file)) return false;                   // source exists?
        return parent::_useCache();
    }

    protected function _addDependencies() {

        // parser cache file dependencies ...
        $files = array($this->file,                              // ... source
                DOKU_INC.'inc/parser/parser.php',                // ... parser
                DOKU_INC.'inc/parser/handler.php',               // ... handler
                );
        $files = array_merge($files, getConfigFiles('main'));    // ... wiki settings

        $this->depends['files'] = !empty($this->depends['files']) ? array_merge($files, $this->depends['files']) : $files;
        parent::_addDependencies();
    }

}

/**
 * Caching of data of renderer
 */
class cache_renderer extends cache_parser {

    /**
     * method contains cache use decision logic
     *
     * @return bool               see useCache()
     */
    public function _useCache() {
        global $conf;

        if (!parent::_useCache()) return false;

        if (!isset($this->page)) {
            return true;
        }

        if ($this->_time < @filemtime(metaFN($this->page,'.meta'))) return false;         // meta cache older than file it depends on?

        // check current link existence is consistent with cache version
        // first check the purgefile
        // - if the cache is more recent than the purgefile we know no links can have been updated
        if ($this->_time >= @filemtime($conf['cachedir'].'/purgefile')) {
            return true;
        }

        // for wiki pages, check metadata dependencies
        $metadata = p_get_metadata($this->page);

        if (!isset($metadata['relation']['references']) ||
                empty($metadata['relation']['references'])) {
            return true;
        }

        foreach ($metadata['relation']['references'] as $id => $exists) {
            if ($exists != page_exists($id,'',false)) return false;
        }

        return true;
    }

    protected function _addDependencies() {
        global $conf;

        // default renderer cache file 'age' is dependent on 'cachetime' setting, two special values:
        //    -1 : do not cache (should not be overridden)
        //    0  : cache never expires (can be overridden) - no need to set depends['age']
        if ($conf['cachetime'] == -1) {
            $this->_nocache = true;
            return;
        } elseif ($conf['cachetime'] > 0) {
            $this->depends['age'] = isset($this->depends['age']) ?
                min($this->depends['age'],$conf['cachetime']) : $conf['cachetime'];
        }

        // renderer cache file dependencies ...
        $files = array(
                DOKU_INC.'inc/parser/'.$this->mode.'.php',       // ... the renderer
                );

        // page implies metadata and possibly some other dependencies
        if (isset($this->page)) {

            $valid = p_get_metadata($this->page, 'date valid');         // for xhtml this will render the metadata if needed
            if (!empty($valid['age'])) {
                $this->depends['age'] = isset($this->depends['age']) ?
                    min($this->depends['age'],$valid['age']) : $valid['age'];
            }
        }

        $this->depends['files'] = !empty($this->depends['files']) ? array_merge($files, $this->depends['files']) : $files;
        parent::_addDependencies();
    }
}

/**
 * Caching of parser instructions
 */
class cache_instructions extends cache_parser {

    /**
     * @param string $id page id
     * @param string $file source file for cache
     */
    public function __construct($id, $file) {
        parent::__construct($id, $file, 'i');
    }

    /**
     * retrieve the cached data
     *
     * @param   bool   $clean   true to clean line endings, false to leave line endings alone
     * @return  array          cache contents
     */
    public function retrieveCache($clean=true) {
        $contents = io_readFile($this->cache, false);
        return !empty($contents) ? unserialize($contents) : array();
    }

    /**
     * cache $instructions
     *
     * @param   array $instructions  the instruction to be cached
     * @return  bool                  true on success, false otherwise
     */
    public function storeCache($instructions) {
        if ($this->_nocache) return false;

        return io_savefile($this->cache,serialize($instructions));
    }
}
