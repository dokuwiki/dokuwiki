<?php

namespace dokuwiki\Cache;

/**
 * Caching of parser instructions
 */
class CacheInstructions extends \dokuwiki\Cache\CacheParser
{

    /**
     * @param string $id page id
     * @param string $file source file for cache
     */
    public function __construct($id, $file)
    {
        parent::__construct($id, $file, 'i');
    }

    /**
     * retrieve the cached data
     *
     * @param   bool $clean true to clean line endings, false to leave line endings alone
     * @return  array          cache contents
     */
    public function retrieveCache($clean = true)
    {
        $contents = io_readFile($this->cache, false);
        return !empty($contents) ? unserialize($contents) : array();
    }

    /**
     * cache $instructions
     *
     * @param   array $instructions the instruction to be cached
     * @return  bool                  true on success, false otherwise
     */
    public function storeCache($instructions)
    {
        if ($this->_nocache) {
            return false;
        }

        return io_saveFile($this->cache, serialize($instructions));
    }
}
