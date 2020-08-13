<?php
/**
 * Simulates a full DokuWiki HTTP Request and allows
 * runtime inspection.
 */

use dokuwiki\Input\Input;

/**
 * Helper class to execute a fake request
 */
class TestRequest {

    protected $valid_scripts = array('/doku.php', '/lib/exe/fetch.php', '/lib/exe/detail.php', '/lib/exe/ajax.php');
    protected $script;

    protected $server = array();
    protected $session = array();
    protected $get = array();
    protected $post = array();
    protected $data = array();

    /** @var string stores the output buffer, even when it's flushed */
    protected $output_buffer = '';

    /** @var null|TestRequest the currently running request */
    static protected $running = null;

    /**
     * Get a $_SERVER var
     *
     * @param string $key
     * @return mixed
     */
    public function getServer($key) {
        return $this->server[$key];
    }

    /**
     * Get a $_SESSION var
     *
     * @param string $key
     * @return mixed
     */
    public function getSession($key) {
        return $this->session[$key];
    }

    /**
     * Get a $_GET var
     *
     * @param string $key
     * @return mixed
     */
    public function getGet($key) {
        return $this->get[$key];
    }

    /**
     * Get a $_POST var
     *
     * @param string $key
     * @return mixed
     */
    public function getPost($key) {
        return $this->post[$key];
    }

    /**
     * Get the script that will execute the request
     *
     * @return string
     */
    public function getScript() {
        return $this->script;
    }

    /**
     * Set a $_SERVER var
     *
     * @param string $key
     * @param mixed $value
     */
    public function setServer($key, $value) {
        $this->server[$key] = $value;
    }

    /**
     * Set a $_SESSION var
     *
     * @param string $key
     * @param mixed $value
     */
    public function setSession($key, $value) {
        $this->session[$key] = $value;
    }

    /**
     * Set a $_GET var
     *
     * @param string $key
     * @param mixed $value
     */
    public function setGet($key, $value) {
        $this->get[$key] = $value;
    }

    /**
     * Set a $_POST var
     *
     * @param string $key
     * @param mixed $value
     */
    public function setPost($key, $value) {
        $this->post[$key] = $value;
    }

    /**
     * Executes the request
     *
     * @param string $uri end URL to simulate, needs to be one of the testable scripts
     * @return TestResponse the resulting output of the request
     */
    public function execute($uri = '/doku.php') {
        global $INPUT;

        // save old environment
        $server = $_SERVER;
        $session = $_SESSION;
        $get = $_GET;
        $post = $_POST;
        $request = $_REQUEST;
        $input = $INPUT;

        // prepare the right URI
        $this->setUri($uri);

        // import all defined globals into the function scope
        foreach(array_keys($GLOBALS) as $glb) {
            global $$glb;
        }

        // fake environment
        global $default_server_vars;
        $_SERVER = array_merge($default_server_vars, $this->server);
        $_SESSION = $this->session;
        $_GET = $this->get;
        $_POST = $this->post;
        $_REQUEST = array_merge($_GET, $_POST);

        // reset output buffer
        $this->output_buffer = '';

        // now execute dokuwiki and grep the output
        self::$running = $this;
        header_remove();
        ob_start(array($this, 'ob_start_callback'));
        $INPUT = new Input();
        include(DOKU_INC . $this->script);
        ob_end_flush();
        self::$running = null;

        // create the response object
        $response = new TestResponse(
            $this->output_buffer,
            // cli sapi doesn't do headers, prefer xdebug_get_headers() which works under cli
            (function_exists('xdebug_get_headers') ? xdebug_get_headers() : headers_list()),
            $this->data
        );

        // reset environment
        $_SERVER = $server;
        $_SESSION = $session;
        $_GET = $get;
        $_POST = $post;
        $_REQUEST = $request;
        $INPUT = $input;

        return $response;
    }

    /**
     * Set the virtual URI the request works against
     *
     * This parses the given URI and sets any contained GET variables
     * but will not overwrite any previously set ones (eg. set via setGet()).
     *
     * It initializes the $_SERVER['REQUEST_URI'] and $_SERVER['QUERY_STRING']
     * with all set GET variables.
     *
     * @param string $uri end URL to simulate
     * @throws Exception when an invalid script is passed
     */
    protected function setUri($uri) {
        if(!preg_match('#^(' . join('|', $this->valid_scripts) . ')#', $uri)) {
            throw new Exception("$uri \n--- only " . join(', ', $this->valid_scripts) . " are supported currently");
        }

        $params = array();
        list($uri, $query) = explode('?', $uri, 2);
        if($query) parse_str($query, $params);

        $this->script = substr($uri, 1);
        $this->get = array_merge($params, $this->get);
        if(count($this->get)) {
            $query = '?' . http_build_query($this->get, '', '&');
            $query = str_replace(
                array('%3A', '%5B', '%5D'),
                array(':', '[', ']'),
                $query
            );
            $uri = $uri . $query;
        }

        $this->setServer('QUERY_STRING', $query);
        $this->setServer('REQUEST_URI', $uri);
    }

    /**
     * Simulate a POST request with the given variables
     *
     * @param array $post all the POST parameters to use
     * @param string $uri end URL to simulate
     * @return TestResponse
     */
    public function post($post = array(), $uri = '/doku.php') {
        $this->post = array_merge($this->post, $post);
        $this->setServer('REQUEST_METHOD', 'POST');
        return $this->execute($uri);
    }

    /**
     * Simulate a GET request with the given variables
     *
     * @param array $get all the GET parameters to use
     * @param string $uri end URL to simulate
     * @return TestResponse
     */
    public function get($get = array(), $uri = '/doku.php') {
        $this->get = array_merge($this->get, $get);
        $this->setServer('REQUEST_METHOD', 'GET');
        return $this->execute($uri);
    }

    /**
     * Callback for ob_start
     *
     * This continues to fill our own buffer, even when some part
     * of the code askes for flushing the buffers
     *
     * @param string $buffer
     */
    public function ob_start_callback($buffer) {
        $this->output_buffer .= $buffer;
    }

    /**
     * Access the TestRequest from the executed code
     *
     * This allows certain functions to access the TestRequest that is accessing them
     * to add additional info.
     *
     * @return null|TestRequest the currently executed request if any
     */
    public static function getRunning() {
        return self::$running;
    }

    /**
     * Store data to be read in the response later
     *
     * When called multiple times with the same key, the data is appended to this
     * key's array
     *
     * @param string $key the identifier for this information
     * @param mixed $value arbitrary data to store
     */
    public function addData($key, $value) {
        if(!isset($this->data[$key])) $this->data[$key] = array();
        $this->data[$key][] = $value;
    }
}
