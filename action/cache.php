<?php
use dokuwiki\plugin\struct\meta\SearchConfig;
use dokuwiki\plugin\struct\meta\SearchConfigParameters;

/**
 * Handle caching of pages containing struct aggregations
 */
class action_plugin_struct_cache extends DokuWiki_Action_Plugin {

    /**
     * Registers a callback function for a given event
     *
     * @param Doku_Event_Handler $controller DokuWiki's event controller object
     * @return void
     */
    public function register(Doku_Event_Handler $controller) {
        $controller->register_hook('PARSER_CACHE_USE', 'BEFORE', $this, 'handle_cache_aggregation');
        $controller->register_hook('PARSER_CACHE_USE', 'AFTER', $this, 'handle_cache_dynamic');
    }

    /**
     * For pages containing an aggregation, add the last modified date of the database itself
     * to the cache dependencies
     *
     * @param Doku_Event $event event object by reference
     * @param mixed $param [the parameters passed as fifth argument to register_hook() when this
     *                           handler was registered]
     * @return bool
     */
    public function handle_cache_aggregation(Doku_Event $event, $param) {
        global $INPUT;

        /** @var \cache_parser $cache */
        $cache = $event->data;
        if($cache->mode != 'xhtml') return true;
        if(!$cache->page) return true; // not a page cache

        $meta = p_get_metadata($cache->page, 'plugin struct');
        if(isset($meta['hasaggregation'])) {
            /** @var helper_plugin_struct_db $db */
            $db = plugin_load('helper', 'struct_db');
            // cache depends on last database save
            $cache->depends['files'][] = $db->getDB()->getAdapter()->getDbFile();

            // dynamic renders should never overwrite the default page cache
            // we need this in additon to handle_cache_dynamic() below because we can only
            // influence if a cache is used, not that it will be written
            if(
                $INPUT->has(SearchConfigParameters::$PARAM_FILTER) ||
                $INPUT->has(SearchConfigParameters::$PARAM_OFFSET) ||
                $INPUT->has(SearchConfigParameters::$PARAM_SORT)
            ) {
                $cache->key .= 'dynamic';
            }

            // cache depends on today's date
            if($meta['hasaggregation'] & SearchConfig::$CACHE_DATE) {
                $oldage = $cache->depends['age'];
                $newage = time() - mktime(0, 0, 1); // time since first second today
                $cache->depends['age'] = min($oldage, $newage);
            }

            // cache depends on current user
            if($meta['hasaggregation'] & SearchConfig::$CACHE_USER) {
                $cache->key .= ';'.$INPUT->server->str('REMOTE_USER');

            }

            // rebuild cachename
            $cache->cache = getCacheName($cache->key, $cache->ext);
        }

        return true;
    }

    /**
     * Disable cache when dymanic parameters are present
     *
     * @param Doku_Event $event event object by reference
     * @param mixed $param [the parameters passed as fifth argument to register_hook() when this
     *                           handler was registered]
     * @return bool
     */
    public function handle_cache_dynamic(Doku_Event $event, $param) {
        /** @var \cache_parser $cache */
        $cache = $event->data;
        if($cache->mode != 'xhtml') return true;
        if(!$cache->page) return true; // not a page cache
        global $INPUT;

        // disable cache use when one of these parameters is present
        foreach(array(
                    SearchConfigParameters::$PARAM_FILTER,
                    SearchConfigParameters::$PARAM_OFFSET,
                    SearchConfigParameters::$PARAM_SORT
                ) as $key) {
            if($INPUT->has($key)) {
                $event->result = false;
                return true;
            }
        }

        return true;
    }

}
