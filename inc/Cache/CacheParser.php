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
        global $INPUT;

        if ($id) {
            $this->page = $id;
        }
        $this->file = $file;
        $this->mode = $mode;

        $this->setEvent('PARSER_CACHE_USE');
        parent::__construct($file . $INPUT->server->str('HTTP_HOST') . $INPUT->server->str('SERVER_PORT'), '.' . $mode);
    }

    /**
     * method contains cache use decision logic
     *
     * @return bool see useCache()
     */
    public function makeDefaultCacheDecision()
    {
        if (!file_exists($this->file)) {
            // source doesn't exist
            return false;
        }
        return parent::makeDefaultCacheDecision();
    }

    protected function addDependencies()
    {
        // parser cache file dependencies ...
        $files = [
            $this->file, // source
            DOKU_INC . 'inc/Parsing/Parser.php', // parser
            DOKU_INC . 'inc/parser/handler.php', // handler
        ];
        $files = array_merge($files, getConfigFiles('main')); // wiki settings

        $this->depends['files'] = empty($this->depends['files']) ?
            $files :
            array_merge($files, $this->depends['files']);
        parent::addDependencies();
    }
}
