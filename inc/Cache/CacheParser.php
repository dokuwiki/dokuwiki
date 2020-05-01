<?php

namespace dokuwiki\Cache;

/**
 * Parser caching
 */
class CacheParser extends Cache
{

    public $file = '';       // source file for cache
    public $mode = '';       // input mode (represents the processing the input file will undergo)
    public $page = '';

    /**
     *
     * @param string $id page id
     * @param string $file source file for cache
     * @param string $mode input mode
     */
    public function __construct($id, $file, $mode)
    {
        if ($id) {
            $this->page = $id;
        }
        $this->file = $file;
        $this->mode = $mode;

        $this->setEvent('PARSER_CACHE_USE');
        parent::__construct($file . $_SERVER['HTTP_HOST'] . $_SERVER['SERVER_PORT'], '.' . $mode);
    }

    /**
     * method contains cache use decision logic
     *
     * @return bool               see useCache()
     */
    public function makeDefaultCacheDecision()
    {

        if (!file_exists($this->file)) {
            return false;
        }                   // source exists?
        return parent::makeDefaultCacheDecision();
    }

    protected function addDependencies()
    {

        // parser cache file dependencies ...
        $files = array(
            $this->file,                              // ... source
            DOKU_INC . 'inc/parser/Parser.php',                // ... parser
            DOKU_INC . 'inc/parser/handler.php',               // ... handler
        );
        $files = array_merge($files, getConfigFiles('main'));    // ... wiki settings

        $this->depends['files'] = !empty($this->depends['files']) ?
            array_merge($files, $this->depends['files']) :
            $files;
        parent::addDependencies();
    }

}
