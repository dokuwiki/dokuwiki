<?php

namespace dokuwiki\Cache;

/**
 * Caching of data of renderer
 */
class CacheRenderer extends CacheParser
{

    /**
     * method contains cache use decision logic
     *
     * @return bool               see useCache()
     */
    public function makeDefaultCacheDecision()
    {
        global $conf;

        if (!parent::makeDefaultCacheDecision()) {
            return false;
        }

        if (!isset($this->page)) {
            return true;
        }

        // meta cache older than file it depends on?
        if ($this->_time < @filemtime(metaFN($this->page, '.meta'))) {
            return false;
        }

        // check current link existence is consistent with cache version
        // first check the purgefile
        // - if the cache is more recent than the purgefile we know no links can have been updated
        if ($this->_time >= @filemtime($conf['cachedir'] . '/purgefile')) {
            return true;
        }

        // for wiki pages, check metadata dependencies
        $metadata = p_get_metadata($this->page);

        if (!isset($metadata['relation']['references']) ||
            empty($metadata['relation']['references'])) {
            return true;
        }

        foreach ($metadata['relation']['references'] as $id => $exists) {
            if ($exists != page_exists($id, '', false)) {
                return false;
            }
        }

        return true;
    }

    protected function addDependencies()
    {
        global $conf;

        // default renderer cache file 'age' is dependent on 'cachetime' setting, two special values:
        //    -1 : do not cache (should not be overridden)
        //    0  : cache never expires (can be overridden) - no need to set depends['age']
        if ($conf['cachetime'] == -1) {
            $this->_nocache = true;
            return;
        } elseif ($conf['cachetime'] > 0) {
            $this->depends['age'] = isset($this->depends['age']) ?
                min($this->depends['age'], $conf['cachetime']) : $conf['cachetime'];
        }

        // renderer cache file dependencies ...
        $files = array(
            DOKU_INC . 'inc/parser/' . $this->mode . '.php',       // ... the renderer
        );

        // page implies metadata and possibly some other dependencies
        if (isset($this->page)) {

            // for xhtml this will render the metadata if needed
            $valid = p_get_metadata($this->page, 'date valid');
            if (!empty($valid['age'])) {
                $this->depends['age'] = isset($this->depends['age']) ?
                    min($this->depends['age'], $valid['age']) : $valid['age'];
            }
        }

        $this->depends['files'] = !empty($this->depends['files']) ?
            array_merge($files, $this->depends['files']) :
            $files;

        parent::addDependencies();
    }
}
