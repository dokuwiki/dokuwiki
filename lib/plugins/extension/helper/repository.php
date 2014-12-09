<?php
/**
 * DokuWiki Plugin extension (Helper Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Michael Hamann <michael@content-space.de>
 */

#define('EXTENSION_REPOSITORY_API', 'http://localhost/dokuwiki/lib/plugins/pluginrepo/api.php');

if (!defined('EXTENSION_REPOSITORY_API_ENDPOINT'))
    define('EXTENSION_REPOSITORY_API', 'http://www.dokuwiki.org/lib/plugins/pluginrepo/api.php');

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

/**
 * Class helper_plugin_extension_repository provides access to the extension repository on dokuwiki.org
 */
class helper_plugin_extension_repository extends DokuWiki_Plugin {
    private $loaded_extensions = array();
    private $has_access = null;
    /**
     * Initialize the repository (cache), fetches data for all installed plugins
     */
    public function init() {
        /* @var Doku_Plugin_Controller $plugin_controller */
        global $plugin_controller;
        if ($this->hasAccess()) {
            $list = $plugin_controller->getList('', true);
            $request_data = array('fmt' => 'php');
            $request_needed = false;
            foreach ($list as $name) {
                $cache = new cache('##extension_manager##'.$name, '.repo');

                if (!isset($this->loaded_extensions[$name]) && $this->hasAccess() && !$cache->useCache(array('age' => 3600 * 24))) {
                    $this->loaded_extensions[$name] = true;
                    $request_data['ext'][] = $name;
                    $request_needed = true;
                }
            }

            if ($request_needed) {
                $httpclient = new DokuHTTPClient();
                $data = $httpclient->post(EXTENSION_REPOSITORY_API, $request_data);
                if ($data !== false) {
                    $extensions = unserialize($data);
                    foreach ($extensions as $extension) {
                        $cache = new cache('##extension_manager##'.$extension['plugin'], '.repo');
                        $cache->storeCache(serialize($extension));
                    }
                } else {
                    $this->has_access = false;
                }
            }
        }
    }

    /**
     * If repository access is available
     *
     * @return bool If repository access is available
     */
    public function hasAccess() {
        if ($this->has_access === null) {
            $cache = new cache('##extension_manager###hasAccess', '.repo');

            if (!$cache->useCache(array('age' => 3600 * 24, 'purge'=>1))) {
                $httpclient = new DokuHTTPClient();
                $httpclient->timeout = 5;
                $data = $httpclient->get(EXTENSION_REPOSITORY_API.'?cmd=ping');
                if ($data !== false) {
                    $this->has_access = true;
                    $cache->storeCache(1);
                } else {
                    $this->has_access = false;
                    $cache->storeCache(0);
                }
            } else {
                $this->has_access = ($cache->retrieveCache(false) == 1);
            }
        }
        return $this->has_access;
    }

    /**
     * Get the remote data of an individual plugin or template
     *
     * @param string $name  The plugin name to get the data for, template names need to be prefix by 'template:'
     * @return array The data or null if nothing was found (possibly no repository access)
     */
    public function getData($name) {
        $cache = new cache('##extension_manager##'.$name, '.repo');

        if (!isset($this->loaded_extensions[$name]) && $this->hasAccess() && !$cache->useCache(array('age' => 3600 * 24))) {
            $this->loaded_extensions[$name] = true;
            $httpclient = new DokuHTTPClient();
            $data = $httpclient->get(EXTENSION_REPOSITORY_API.'?fmt=php&ext[]='.urlencode($name));
            if ($data !== false) {
                $result = unserialize($data);
                $cache->storeCache(serialize($result[0]));
                return $result[0];
            } else {
                $this->has_access = false;
            }
        }
        if (file_exists($cache->cache)) {
            return unserialize($cache->retrieveCache(false));
        }
        return array();
    }

    /**
     * Search for plugins or templates using the given query string
     *
     * @param string $q the query string
     * @return array a list of matching extensions
     */
    public function search($q){
        $query = $this->parse_query($q);
        $query['fmt'] = 'php';

        $httpclient = new DokuHTTPClient();
        $data = $httpclient->post(EXTENSION_REPOSITORY_API, $query);
        if ($data === false) return array();
        $result = unserialize($data);

        $ids = array();

        // store cache info for each extension
        foreach($result as $ext){
            $name = $ext['plugin'];
            $cache = new cache('##extension_manager##'.$name, '.repo');
            $cache->storeCache(serialize($ext));
            $ids[] = $name;
        }

        return $ids;
    }

    /**
     * Parses special queries from the query string
     *
     * @param string $q
     * @return array
     */
    protected function parse_query($q){
        $parameters = array(
            'tag'  => array(),
            'mail' => array(),
            'type' => array(),
            'ext'  => array()
        );

        // extract tags
        if(preg_match_all('/(^|\s)(tag:([\S]+))/', $q, $matches, PREG_SET_ORDER)){
            foreach($matches as $m){
                $q = str_replace($m[2], '', $q);
                $parameters['tag'][] = $m[3];
            }
        }
        // extract author ids
        if(preg_match_all('/(^|\s)(authorid:([\S]+))/', $q, $matches, PREG_SET_ORDER)){
            foreach($matches as $m){
                $q = str_replace($m[2], '', $q);
                $parameters['mail'][] = $m[3];
            }
        }
        // extract extensions
        if(preg_match_all('/(^|\s)(ext:([\S]+))/', $q, $matches, PREG_SET_ORDER)){
            foreach($matches as $m){
                $q = str_replace($m[2], '', $q);
                $parameters['ext'][] = $m[3];
            }
        }
        // extract types
        if(preg_match_all('/(^|\s)(type:([\S]+))/', $q, $matches, PREG_SET_ORDER)){
            foreach($matches as $m){
                $q = str_replace($m[2], '', $q);
                $parameters['type'][] = $m[3];
            }
        }

        // FIXME make integer from type value

        $parameters['q'] = trim($q);
        return $parameters;
    }
}

// vim:ts=4:sw=4:et:
