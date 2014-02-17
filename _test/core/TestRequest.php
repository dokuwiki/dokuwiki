<?php
/**
 * Simulates a full DokuWiki HTTP Request and allows
 * runtime inspection.
 */

// output buffering
$output_buffer = '';

function ob_start_callback($buffer) {
    global $output_buffer;
    $output_buffer .= $buffer;
}


/**
 * Helper class to execute a fake request
 */
class TestRequest {

    private $valid_scripts = array('/doku.php', '/lib/exe/fetch.php', '/lib/exe/detail.php');
    private $script;

    private $server = array();
    private $session = array();
    private $get = array();
    private $post = array();

    public function getServer($key) { return $this->server[$key]; }
    public function getSession($key) { return $this->session[$key]; }
    public function getGet($key) { return $this->get[$key]; }
    public function getPost($key) { return $this->post[$key]; }
    public function getScript() { return $this->script; }

    public function setServer($key, $value) { $this->server[$key] = $value; }
    public function setSession($key, $value) { $this->session[$key] = $value; }
    public function setGet($key, $value) { $this->get[$key] = $value; }
    public function setPost($key, $value) { $this->post[$key] = $value; }

    /**
     * Executes the request
     *
     * @param string $url  end URL to simulate, needs to start with /doku.php currently
     * @return TestResponse the resulting output of the request
     */
    public function execute($uri='/doku.php') {
        global $INPUT;
        global $ID;
        global $INFO;

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
        foreach(array_keys($GLOBALS) as $glb){
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
        global $output_buffer;
        $output_buffer = '';

        // now execute dokuwiki and grep the output
        header_remove();
        ob_start('ob_start_callback');
        $INPUT = new Input();
        include(DOKU_INC.$this->script);
        ob_end_flush();

        // create the response object
        $response = new TestResponse(
            $output_buffer,
            (function_exists('xdebug_get_headers') ? xdebug_get_headers() : headers_list())   // cli sapi doesn't do headers, prefer xdebug_get_headers() which works under cli
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
     * @param string $url  end URL to simulate, needs to start with /doku.php currently
     * @todo make this work with other end points
     */
    protected function setUri($uri){
        if(!preg_match('#^('.join('|',$this->valid_scripts).')#',$uri)){
            throw new Exception("$uri \n--- only ".join(', ',$this->valid_scripts)." are supported currently");
        }

        $params = array();
        list($uri, $query) = explode('?',$uri,2);
        if($query) parse_str($query, $params);

        $this->script = substr($uri,1);
        $this->get  = array_merge($params, $this->get);
        if(count($this->get)){
            $query = '?'.http_build_query($this->get, '', '&');
            $query = str_replace(
                array('%3A', '%5B', '%5D'),
                array(':', '[', ']'),
                $query
            );
            $uri = $uri.$query;
        }

        $this->setServer('QUERY_STRING', $query);
        $this->setServer('REQUEST_URI', $uri);
    }

    /**
     * Simulate a POST request with the given variables
     *
     * @param array $post  all the POST parameters to use
     * @param string $url  end URL to simulate, needs to start with /doku.php, /lib/exe/fetch.php or /lib/exe/detail.php currently
     * @param return TestResponse
     */
    public function post($post=array(), $uri='/doku.php') {
        $this->post = array_merge($this->post, $post);
        $this->setServer('REQUEST_METHOD', 'POST');
        return $this->execute($uri);
    }

    /**
     * Simulate a GET request with the given variables
     *
     * @param array $GET   all the GET parameters to use
     * @param string $url  end URL to simulate, needs to start with /doku.php, /lib/exe/fetch.php or /lib/exe/detail.php currently
     * @param return TestResponse
     */
    public function get($get=array(), $uri='/doku.php') {
        $this->get  = array_merge($this->get, $get);
        $this->setServer('REQUEST_METHOD', 'GET');
        return $this->execute($uri);
    }


}
