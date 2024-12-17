<?php

namespace dokuwiki\plugin\extension;

use dokuwiki\Cache\Cache;
use dokuwiki\HTTP\DokuHTTPClient;
use JsonException;

class Repository
{
    public const EXTENSION_REPOSITORY_API = 'https://www.dokuwiki.org/lib/plugins/pluginrepo/api.php';

    protected const CACHE_PREFIX = '##extension_manager##';
    protected const CACHE_SUFFIX = '.repo';
    protected const CACHE_TIME = 3600 * 24;

    protected static $instance;
    protected $hasAccess;

    /**
     * Protected Constructor
     *
     * Use Repository::getInstance() to get an instance
     */
    protected function __construct()
    {
    }

    /**
     * @return Repository
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Check if access to the repository is possible
     *
     * On the first call this will throw an exception if access is not possible. On subsequent calls
     * it will return the cached result. Thus it is recommended to call this method once when instantiating
     * the repository for the first time and handle the exception there. Subsequent calls can then be used
     * to access cached data.
     *
     * @return bool
     * @throws Exception
     */
    public function checkAccess()
    {
        if ($this->hasAccess !== null) {
            return $this->hasAccess; // we already checked
        }

        // check for SSL support
        if (!in_array('ssl', stream_get_transports())) {
            throw new Exception('nossl');
        }

        // ping the API
        $httpclient = new DokuHTTPClient();
        $httpclient->timeout = 5;
        $data = $httpclient->get(self::EXTENSION_REPOSITORY_API . '?cmd=ping');
        if ($data === false) {
            $this->hasAccess = false;
            throw new Exception('repo_error');
        } elseif ($data !== '1') {
            $this->hasAccess = false;
            throw new Exception('repo_badresponse');
        } else {
            $this->hasAccess = true;
        }
        return $this->hasAccess;
    }

    /**
     * Fetch the data for multiple extensions from the repository
     *
     * @param string[] $ids A list of extension ids
     * @throws Exception
     */
    protected function fetchExtensions($ids)
    {
        if (!$this->checkAccess()) return;

        $httpclient = new DokuHTTPClient();
        $data = [
            'fmt' => 'json',
            'ext' => $ids
        ];

        $response = $httpclient->post(self::EXTENSION_REPOSITORY_API, $data);
        if ($response === false) {
            $this->hasAccess = false;
            throw new Exception('repo_error');
        }

        try {
            $found = [];
            $extensions = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
            foreach ($extensions as $extension) {
                $this->storeCache($extension['plugin'], $extension);
                $found[] = $extension['plugin'];
            }
            // extensions that have not been returned are not in the repository, but we should cache that too
            foreach (array_diff($ids, $found) as $id) {
                $this->storeCache($id, []);
            }
        } catch (JsonException $e) {
            $this->hasAccess = false;
            throw new Exception('repo_badresponse', 0, $e);
        }
    }

    /**
     * This creates a list of Extension objects from the given list of ids
     *
     * The extensions are initialized by fetching their data from the cache or the repository.
     * This is the recommended way to initialize a whole bunch of extensions at once as it will only do
     * a single API request for all extensions that are not in the cache.
     *
     * Extensions that are not found in the cache or the repository will be initialized as null.
     *
     * @param string[] $ids
     * @return (Extension|null)[] [id => Extension|null, ...]
     * @throws Exception
     */
    public function initExtensions($ids)
    {
        $result = [];
        $toload = [];

        // first get all that are cached
        foreach ($ids as $id) {
            $data = $this->retrieveCache($id);
            if ($data === null || $data === []) {
                $toload[] = $id;
            } else {
                $result[$id] = Extension::createFromRemoteData($data);
            }
        }

        // then fetch the rest at once
        if ($toload) {
            $this->fetchExtensions($toload);
            foreach ($toload as $id) {
                $data = $this->retrieveCache($id);
                if ($data === null || $data === []) {
                    $result[$id] = null;
                } else {
                    $result[$id] = Extension::createFromRemoteData($data);
                }
            }
        }

        return $result;
    }

    /**
     * Initialize a new Extension object from remote data for the given id
     *
     * @param string $id
     * @return Extension|null
     * @throws Exception
     */
    public function initExtension($id)
    {
        $result = $this->initExtensions([$id]);
        return $result[$id];
    }

    /**
     * Get the pure API data for a single extension
     *
     * Used when lazy loading remote data in Extension
     *
     * @param string $id
     * @return array|null
     * @throws Exception
     */
    public function getExtensionData($id)
    {
        $data = $this->retrieveCache($id);
        if ($data === null) {
            $this->fetchExtensions([$id]);
            $data = $this->retrieveCache($id);
        }
        return $data;
    }

    /**
     * Search for extensions using the given query string
     *
     * @param string $q the query string
     * @return Extension[] a list of matching extensions
     * @throws Exception
     */
    public function searchExtensions($q)
    {
        if (!$this->checkAccess()) return [];

        $query = $this->parseQuery($q);
        $query['fmt'] = 'json';

        $httpclient = new DokuHTTPClient();
        $response = $httpclient->post(self::EXTENSION_REPOSITORY_API, $query);
        if ($response === false) {
            $this->hasAccess = false;
            throw new Exception('repo_error');
        }

        try {
            $items = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            $this->hasAccess = false;
            throw new Exception('repo_badresponse', 0, $e);
        }

        $results = [];
        foreach ($items as $item) {
            $this->storeCache($item['plugin'], $item);
            $results[] = Extension::createFromRemoteData($item);
        }
        return $results;
    }

    /**
     * Parses special queries from the query string
     *
     * @param string $q
     * @return array
     */
    protected function parseQuery($q)
    {
        $parameters = [
            'tag' => [],
            'mail' => [],
            'type' => 0,
            'ext' => []
        ];

        // extract tags
        if (preg_match_all('/(^|\s)(tag:([\S]+))/', $q, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $q = str_replace($m[2], '', $q);
                $parameters['tag'][] = $m[3];
            }
        }
        // extract author ids
        if (preg_match_all('/(^|\s)(authorid:([\S]+))/', $q, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $q = str_replace($m[2], '', $q);
                $parameters['mail'][] = $m[3];
            }
        }
        // extract extensions
        if (preg_match_all('/(^|\s)(ext:([\S]+))/', $q, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $q = str_replace($m[2], '', $q);
                $parameters['ext'][] = $m[3];
            }
        }
        // extract types
        if (preg_match_all('/(^|\s)(type:([\S]+))/', $q, $matches, PREG_SET_ORDER)) {
            $typevalues = array_flip(Extension::COMPONENT_TYPES);
            $typevalues = array_change_key_case($typevalues, CASE_LOWER);

            foreach ($matches as $m) {
                $q = str_replace($m[2], '', $q);
                $t = strtolower($m[3]);
                if (isset($typevalues[$t])) {
                    $parameters['type'] += $typevalues[$t];
                }
            }
        }

        $parameters['q'] = trim($q);
        return $parameters;
    }


    /**
     * Store the data for a single extension in the cache
     *
     * @param string $id
     * @param array $data
     */
    protected function storeCache($id, $data)
    {
        $cache = new Cache(self::CACHE_PREFIX . $id, self::CACHE_SUFFIX);
        $cache->storeCache(serialize($data));
    }

    /**
     * Retrieve the data for a single extension from the cache
     *
     * @param string $id
     * @return array|null the data or null if not in cache
     */
    protected function retrieveCache($id)
    {
        $cache = new Cache(self::CACHE_PREFIX . $id, self::CACHE_SUFFIX);
        if ($cache->useCache(['age' => self::CACHE_TIME])) {
            return unserialize($cache->retrieveCache(false));
        }
        return null;
    }
}
