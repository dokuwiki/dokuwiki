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

    private $server = array();
    private $session = array();
    private $get = array();
    private $post = array();

    public function getServer($key) { return $this->server[$key]; }
    public function getSession($key) { return $this->session[$key]; }
    public function getGet($key) { return $this->get[$key]; }
    public function getPost($key) { return $this->post[$key]; }

    public function setServer($key, $value) { $this->server[$key] = $value; }
    public function setSession($key, $value) { $this->session[$key] = $value; }
    public function setGet($key, $value) { $this->get[$key] = $value; }
    public function setPost($key, $value) { $this->post[$key] = $value; }

    /**
     * Executes the request
     *
     * @return TestResponse the resulting output of the request
     */
    public function execute() {
        // save old environment
        $server = $_SERVER;
        $session = $_SESSION;
        $get = $_GET;
        $post = $_POST;
        $request = $_REQUEST;

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
        include(DOKU_INC.'doku.php');
        ob_end_flush();

        // create the response object
        $response = new TestResponse(
            $output_buffer,
            headers_list()
        );

        // reset environment
        $_SERVER = $server;
        $_SESSION = $session;
        $_GET = $get;
        $_POST = $post;
        $_REQUEST = $request;

        return $response;
    }
}
