<?php

namespace dokuwiki\Cache;

use dokuwiki\Debug\PropertyDeprecationHelper;
use dokuwiki\Extension\Event;

/**
 * Generic handling of caching
 */
class Cache
{
    use PropertyDeprecationHelper;

    public $key = '';          // primary identifier for this item
    public $ext = '';          // file ext for cache data, secondary identifier for this item
    public $cache = '';        // cache file name
    public $depends = array(); // array containing cache dependency information,
    //   used by makeDefaultCacheDecision to determine cache validity

    // phpcs:disable
    /**
     * @deprecated since 2019-02-02 use the respective getters instead!
     */
    protected $_event = '';       // event to be triggered during useCache
    protected $_time;
    protected $_nocache = false;  // if set to true, cache will not be used or stored
    // phpcs:enable

    /**
     * @param string $key primary identifier
     * @param string $ext file extension
     */
    public function __construct($key, $ext)
    {
        $this->key = $key;
        $this->ext = $ext;
        $this->cache = getCacheName($key, $ext);

        /**
         * @deprecated since 2019-02-02 use the respective getters instead!
         */
        $this->deprecatePublicProperty('_event');
        $this->deprecatePublicProperty('_time');
        $this->deprecatePublicProperty('_nocache');
    }

    public function getTime()
    {
        return $this->_time;
    }

    public function getEvent()
    {
        return $this->_event;
    }

    public function setEvent($event)
    {
        $this->_event = $event;
    }

    /**
     * public method to determine whether the cache can be used
     *
     * to assist in centralisation of event triggering and calculation of cache statistics,
     * don't override this function override makeDefaultCacheDecision()
     *
     * @param  array $depends array of cache dependencies, support dependecies:
     *                            'age'   => max age of the cache in seconds
     *                            'files' => cache must be younger than mtime of each file
     *                                       (nb. dependency passes if file doesn't exist)
     *
     * @return bool    true if cache can be used, false otherwise
     */
    public function useCache($depends = array())
    {
        $this->depends = $depends;
        $this->addDependencies();

        if ($this->getEvent()) {
            return $this->stats(
                Event::createAndTrigger(
                    $this->getEvent(),
                    $this,
                    array($this, 'makeDefaultCacheDecision')
                )
            );
        }

        return $this->stats($this->makeDefaultCacheDecision());
    }

    /**
     * internal method containing cache use decision logic
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
     * @internal This method may only be called by the event handler! Call \dokuwiki\Cache\Cache::useCache instead!
     *
     * @return bool               see useCache()
     */
    public function makeDefaultCacheDecision()
    {
        if ($this->_nocache) {
            return false;
        }                              // caching turned off
        if (!empty($this->depends['purge'])) {
            return false;
        }              // purge requested?
        if (!($this->_time = @filemtime($this->cache))) {
            return false;
        }   // cache exists?

        // cache too old?
        if (!empty($this->depends['age']) && ((time() - $this->_time) > $this->depends['age'])) {
            return false;
        }

        if (!empty($this->depends['files'])) {
            foreach ($this->depends['files'] as $file) {
                if ($this->_time <= @filemtime($file)) {
                    return false;
                }         // cache older than files it depends on?
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
    protected function addDependencies()
    {
        global $INPUT;
        if ($INPUT->has('purge')) {
            $this->depends['purge'] = true;
        }   // purge requested
    }

    /**
     * retrieve the cached data
     *
     * @param   bool $clean true to clean line endings, false to leave line endings alone
     * @return  string          cache contents
     */
    public function retrieveCache($clean = true)
    {
        return io_readFile($this->cache, $clean);
    }

    /**
     * cache $data
     *
     * @param   string $data the data to be cached
     * @return  bool           true on success, false otherwise
     */
    public function storeCache($data)
    {
        if ($this->_nocache) {
            return false;
        }

        return io_saveFile($this->cache, $data);
    }

    /**
     * remove any cached data associated with this cache instance
     */
    public function removeCache()
    {
        @unlink($this->cache);
    }

    /**
     * Record cache hits statistics.
     * (Only when debugging allowed, to reduce overhead.)
     *
     * @param    bool $success result of this cache use attempt
     * @return   bool              pass-thru $success value
     */
    protected function stats($success)
    {
        global $conf;
        static $stats = null;
        static $file;

        if (!$conf['allowdebug']) {
            return $success;
        }

        if (is_null($stats)) {
            $file = $conf['cachedir'] . '/cache_stats.txt';
            $lines = explode("\n", io_readFile($file));

            foreach ($lines as $line) {
                $i = strpos($line, ',');
                $stats[substr($line, 0, $i)] = $line;
            }
        }

        if (isset($stats[$this->ext])) {
            list($ext, $count, $hits) = explode(',', $stats[$this->ext]);
        } else {
            $ext = $this->ext;
            $count = 0;
            $hits = 0;
        }

        $count++;
        if ($success) {
            $hits++;
        }
        $stats[$this->ext] = "$ext,$count,$hits";

        io_saveFile($file, join("\n", $stats));

        return $success;
    }

    /**
     * @return bool
     */
    public function isNoCache()
    {
        return $this->_nocache;
    }
}
