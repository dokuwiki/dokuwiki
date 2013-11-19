<?php
/**
 * HTTP Client
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Goetz <cpuidle@gmx.de>
 */


define('HTTP_NL',"\r\n");


/**
 * Adds DokuWiki specific configs to the HTTP client
 *
 * @author Andreas Goetz <cpuidle@gmx.de>
 */
class DokuHTTPClient extends HTTPClient {

    /**
     * Constructor.
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    function __construct(){
        global $conf;

        // call parent constructor
        parent::__construct();

        // set some values from the config
        $this->proxy_host   = $conf['proxy']['host'];
        $this->proxy_port   = $conf['proxy']['port'];
        $this->proxy_user   = $conf['proxy']['user'];
        $this->proxy_pass   = conf_decodeString($conf['proxy']['pass']);
        $this->proxy_ssl    = $conf['proxy']['ssl'];
        $this->proxy_except = $conf['proxy']['except'];
    }


    /**
     * Wraps an event around the parent function
     *
     * @triggers HTTPCLIENT_REQUEST_SEND
     * @author   Andreas Gohr <andi@splitbrain.org>
     */
    function sendRequest($url,$data='',$method='GET'){
        $httpdata = array('url'    => $url,
                          'data'   => $data,
                          'method' => $method);
        $evt = new Doku_Event('HTTPCLIENT_REQUEST_SEND',$httpdata);
        if($evt->advise_before()){
            $url    = $httpdata['url'];
            $data   = $httpdata['data'];
            $method = $httpdata['method'];
        }
        $evt->advise_after();
        unset($evt);
        return parent::sendRequest($url,$data,$method);
    }

}

class HTTPClientException extends Exception { }

/**
 * This class implements a basic HTTP client
 *
 * It supports POST and GET, Proxy usage, basic authentication,
 * handles cookies and referers. It is based upon the httpclient
 * function from the VideoDB project.
 *
 * @link   http://www.splitbrain.org/go/videodb
 * @author Andreas Goetz <cpuidle@gmx.de>
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Tobias Sarnowski <sarnowski@new-thoughts.org>
 */
class HTTPClient {
    //set these if you like
    var $agent;         // User agent
    var $http;          // HTTP version defaults to 1.0
    var $timeout;       // read timeout (seconds)
    var $cookies;
    var $referer;
    var $max_redirect;
    var $max_bodysize;
    var $max_bodysize_abort = true;  // if set, abort if the response body is bigger than max_bodysize
    var $header_regexp; // if set this RE must match against the headers, else abort
    var $headers;
    var $debug;
    var $start = 0; // for timings
    var $keep_alive = true; // keep alive rocks

    // don't set these, read on error
    var $error;
    var $redirect_count;

    // read these after a successful request
    var $status;
    var $resp_body;
    var $resp_headers;

    // set these to do basic authentication
    var $user;
    var $pass;

    // set these if you need to use a proxy
    var $proxy_host;
    var $proxy_port;
    var $proxy_user;
    var $proxy_pass;
    var $proxy_ssl; //boolean set to true if your proxy needs SSL
    var $proxy_except; // regexp of URLs to exclude from proxy

    // list of kept alive connections
    static $connections = array();

    // what we use as boundary on multipart/form-data posts
    var $boundary = '---DokuWikiHTTPClient--4523452351';

    /**
     * Constructor.
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    function __construct(){
        $this->agent        = 'Mozilla/4.0 (compatible; DokuWiki HTTP Client; '.PHP_OS.')';
        $this->timeout      = 15;
        $this->cookies      = array();
        $this->referer      = '';
        $this->max_redirect = 3;
        $this->redirect_count = 0;
        $this->status       = 0;
        $this->headers      = array();
        $this->http         = '1.0';
        $this->debug        = false;
        $this->max_bodysize = 0;
        $this->header_regexp= '';
        if(extension_loaded('zlib')) $this->headers['Accept-encoding'] = 'gzip';
        $this->headers['Accept'] = 'text/xml,application/xml,application/xhtml+xml,'.
                                   'text/html,text/plain,image/png,image/jpeg,image/gif,*/*';
        $this->headers['Accept-Language'] = 'en-us';
    }


    /**
     * Simple function to do a GET request
     *
     * Returns the wanted page or false on an error;
     *
     * @param  string $url       The URL to fetch
     * @param  bool   $sloppy304 Return body on 304 not modified
     * @return bool|string  response body, false on error
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    function get($url,$sloppy304=false){
        if(!$this->sendRequest($url)) return false;
        if($this->status == 304 && $sloppy304) return $this->resp_body;
        if($this->status < 200 || $this->status > 206) return false;
        return $this->resp_body;
    }

    /**
     * Simple function to do a GET request with given parameters
     *
     * Returns the wanted page or false on an error.
     *
     * This is a convenience wrapper around get(). The given parameters
     * will be correctly encoded and added to the given base URL.
     *
     * @param  string $url       The URL to fetch
     * @param  array  $data      Associative array of parameters
     * @param  bool   $sloppy304 Return body on 304 not modified
     * @return bool|string  response body, false on error
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    function dget($url,$data,$sloppy304=false){
        if(strpos($url,'?')){
            $url .= '&';
        }else{
            $url .= '?';
        }
        $url .= $this->_postEncode($data);
        return $this->get($url,$sloppy304);
    }

    /**
     * Simple function to do a POST request
     *
     * Returns the resulting page or false on an error;
     *
     * @param  string $url       The URL to fetch
     * @param  array  $data      Associative array of parameters
     * @return bool|string  response body, false on error
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    function post($url,$data){
        if(!$this->sendRequest($url,$data,'POST')) return false;
        if($this->status < 200 || $this->status > 206) return false;
        return $this->resp_body;
    }

    /**
     * Send an HTTP request
     *
     * This method handles the whole HTTP communication. It respects set proxy settings,
     * builds the request headers, follows redirects and parses the response.
     *
     * Post data should be passed as associative array. When passed as string it will be
     * sent as is. You will need to setup your own Content-Type header then.
     *
     * @param  string $url    - the complete URL
     * @param  mixed  $data   - the post data either as array or raw data
     * @param  string $method - HTTP Method usually GET or POST.
     * @return bool - true on success
     * @author Andreas Goetz <cpuidle@gmx.de>
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    function sendRequest($url,$data='',$method='GET'){
        $this->start  = $this->_time();
        $this->error  = '';
        $this->status = 0;
        $this->status = 0;
        $this->resp_body = '';
        $this->resp_headers = array();

        // don't accept gzip if truncated bodies might occur
        if($this->max_bodysize &&
           !$this->max_bodysize_abort &&
           $this->headers['Accept-encoding'] == 'gzip'){
            unset($this->headers['Accept-encoding']);
        }

        // parse URL into bits
        $uri = parse_url($url);
        $server = $uri['host'];
        $path   = $uri['path'];
        if(empty($path)) $path = '/';
        if(!empty($uri['query'])) $path .= '?'.$uri['query'];
        if(!empty($uri['port'])) $port = $uri['port'];
        if(isset($uri['user'])) $this->user = $uri['user'];
        if(isset($uri['pass'])) $this->pass = $uri['pass'];

        // proxy setup
        if($this->proxy_host && (!$this->proxy_except || !preg_match('/'.$this->proxy_except.'/i',$url)) ){
            $request_url = $url;
            $server      = $this->proxy_host;
            $port        = $this->proxy_port;
            if (empty($port)) $port = 8080;
        }else{
            $request_url = $path;
            $server      = $server;
            if (!isset($port)) $port = ($uri['scheme'] == 'https') ? 443 : 80;
        }

        // add SSL stream prefix if needed - needs SSL support in PHP
        if($port == 443 || $this->proxy_ssl) $server = 'ssl://'.$server;

        // prepare headers
        $headers               = $this->headers;
        $headers['Host']       = $uri['host'];
        if(!empty($uri['port'])) $headers['Host'].= ':'.$uri['port'];
        $headers['User-Agent'] = $this->agent;
        $headers['Referer']    = $this->referer;

        if($method == 'POST'){
            if(is_array($data)){
                if($headers['Content-Type'] == 'multipart/form-data'){
                    $headers['Content-Type']   = 'multipart/form-data; boundary='.$this->boundary;
                    $data = $this->_postMultipartEncode($data);
                }else{
                    $headers['Content-Type']   = 'application/x-www-form-urlencoded';
                    $data = $this->_postEncode($data);
                }
            }
            $headers['Content-Length'] = strlen($data);
            $rmethod = 'POST';
        }elseif($method == 'GET'){
            $data = ''; //no data allowed on GET requests
        }
        if($this->user) {
            $headers['Authorization'] = 'Basic '.base64_encode($this->user.':'.$this->pass);
        }
        if($this->proxy_user) {
            $headers['Proxy-Authorization'] = 'Basic '.base64_encode($this->proxy_user.':'.$this->proxy_pass);
        }

        // already connected?
        $connectionId = $this->_uniqueConnectionId($server,$port);
        $this->_debug('connection pool', self::$connections);
        $socket = null;
        if (isset(self::$connections[$connectionId])) {
            $this->_debug('reusing connection', $connectionId);
            $socket = self::$connections[$connectionId];
        }
        if (is_null($socket) || feof($socket)) {
            $this->_debug('opening connection', $connectionId);
            // open socket
            $socket = @fsockopen($server,$port,$errno, $errstr, $this->timeout);
            if (!$socket){
                $this->status = -100;
                $this->error = "Could not connect to $server:$port\n$errstr ($errno)";
                return false;
            }

            // try establish a CONNECT tunnel for SSL
            if($this->_ssltunnel($socket, $request_url)){
                // no keep alive for tunnels
                $this->keep_alive = false;
                // tunnel is authed already
                if(isset($headers['Proxy-Authentication'])) unset($headers['Proxy-Authentication']);
            }

            // keep alive?
            if ($this->keep_alive) {
                self::$connections[$connectionId] = $socket;
            } else {
                unset(self::$connections[$connectionId]);
            }
        }

        if ($this->keep_alive && !$this->proxy_host) {
            // RFC 2068, section 19.7.1: A client MUST NOT send the Keep-Alive
            // connection token to a proxy server. We still do keep the connection the
            // proxy alive (well except for CONNECT tunnels)
            $headers['Connection'] = 'Keep-Alive';
        } else {
            $headers['Connection'] = 'Close';
        }

        try {
            //set non-blocking
            stream_set_blocking($socket, false);

            // build request
            $request  = "$method $request_url HTTP/".$this->http.HTTP_NL;
            $request .= $this->_buildHeaders($headers);
            $request .= $this->_getCookies();
            $request .= HTTP_NL;
            $request .= $data;

            $this->_debug('request',$request);
            $this->_sendData($socket, $request, 'request');

            // read headers from socket
            $r_headers = '';
            do{
                $r_line = $this->_readLine($socket, 'headers');
                $r_headers .= $r_line;
            }while($r_line != "\r\n" && $r_line != "\n");

            $this->_debug('response headers',$r_headers);

            // check if expected body size exceeds allowance
            if($this->max_bodysize && preg_match('/\r?\nContent-Length:\s*(\d+)\r?\n/i',$r_headers,$match)){
                if($match[1] > $this->max_bodysize){
                    if ($this->max_bodysize_abort)
                        throw new HTTPClientException('Reported content length exceeds allowed response size');
                    else
                        $this->error = 'Reported content length exceeds allowed response size';
                }
            }

            // get Status
            if (!preg_match('/^HTTP\/(\d\.\d)\s*(\d+).*?\n/', $r_headers, $m))
                throw new HTTPClientException('Server returned bad answer');

            $this->status = $m[2];

            // handle headers and cookies
            $this->resp_headers = $this->_parseHeaders($r_headers);
            if(isset($this->resp_headers['set-cookie'])){
                foreach ((array) $this->resp_headers['set-cookie'] as $cookie){
                    list($cookie)   = explode(';',$cookie,2);
                    list($key,$val) = explode('=',$cookie,2);
                    $key = trim($key);
                    if($val == 'deleted'){
                        if(isset($this->cookies[$key])){
                            unset($this->cookies[$key]);
                        }
                    }elseif($key){
                        $this->cookies[$key] = $val;
                    }
                }
            }

            $this->_debug('Object headers',$this->resp_headers);

            // check server status code to follow redirect
            if($this->status == 301 || $this->status == 302 ){
                if (empty($this->resp_headers['location'])){
                    throw new HTTPClientException('Redirect but no Location Header found');
                }elseif($this->redirect_count == $this->max_redirect){
                    throw new HTTPClientException('Maximum number of redirects exceeded');
                }else{
                    // close the connection because we don't handle content retrieval here
                    // that's the easiest way to clean up the connection
                    fclose($socket);
                    unset(self::$connections[$connectionId]);

                    $this->redirect_count++;
                    $this->referer = $url;
                    // handle non-RFC-compliant relative redirects
                    if (!preg_match('/^http/i', $this->resp_headers['location'])){
                        if($this->resp_headers['location'][0] != '/'){
                            $this->resp_headers['location'] = $uri['scheme'].'://'.$uri['host'].':'.$uri['port'].
                                                            dirname($uri['path']).'/'.$this->resp_headers['location'];
                        }else{
                            $this->resp_headers['location'] = $uri['scheme'].'://'.$uri['host'].':'.$uri['port'].
                                                            $this->resp_headers['location'];
                        }
                    }
                    // perform redirected request, always via GET (required by RFC)
                    return $this->sendRequest($this->resp_headers['location'],array(),'GET');
                }
            }

            // check if headers are as expected
            if($this->header_regexp && !preg_match($this->header_regexp,$r_headers))
                throw new HTTPClientException('The received headers did not match the given regexp');

            //read body (with chunked encoding if needed)
            $r_body    = '';
            if((isset($this->resp_headers['transfer-encoding']) && $this->resp_headers['transfer-encoding'] == 'chunked')
            || (isset($this->resp_headers['transfer-coding']) && $this->resp_headers['transfer-coding'] == 'chunked')){
                $abort = false;
                do {
                    $chunk_size = '';
                    while (preg_match('/^[a-zA-Z0-9]?$/',$byte=$this->_readData($socket,1,'chunk'))){
                        // read chunksize until \r
                        $chunk_size .= $byte;
                        if (strlen($chunk_size) > 128) // set an abritrary limit on the size of chunks
                            throw new HTTPClientException('Allowed response size exceeded');
                    }
                    $this->_readLine($socket, 'chunk');     // readtrailing \n
                    $chunk_size = hexdec($chunk_size);

                    if($this->max_bodysize && $chunk_size+strlen($r_body) > $this->max_bodysize){
                        if ($this->max_bodysize_abort)
                            throw new HTTPClientException('Allowed response size exceeded');
                        $this->error = 'Allowed response size exceeded';
                        $chunk_size = $this->max_bodysize - strlen($r_body);
                        $abort = true;
                    }

                    if ($chunk_size > 0) {
                        $r_body .= $this->_readData($socket, $chunk_size, 'chunk');
                        $byte = $this->_readData($socket, 2, 'chunk'); // read trailing \r\n
                    }
                } while ($chunk_size && !$abort);
            }elseif(isset($this->resp_headers['content-length']) && !isset($this->resp_headers['transfer-encoding'])){
                /* RFC 2616
                 * If a message is received with both a Transfer-Encoding header field and a Content-Length
                 * header field, the latter MUST be ignored.
                 */

                // read up to the content-length or max_bodysize
                // for keep alive we need to read the whole message to clean up the socket for the next read
                if(!$this->keep_alive && $this->max_bodysize && $this->max_bodysize < $this->resp_headers['content-length']){
                    $length = $this->max_bodysize;
                }else{
                    $length = $this->resp_headers['content-length'];
                }

                $r_body = $this->_readData($socket, $length, 'response (content-length limited)', true);
            }elseif( !isset($this->resp_headers['transfer-encoding']) && $this->max_bodysize && !$this->keep_alive){
                $r_body = $this->_readData($socket, $this->max_bodysize, 'response (content-length limited)', true);
            }else{
                // read entire socket
                $r_size = 0;
                while (!feof($socket)) {
                    $r_body .= $this->_readData($socket, 4096, 'response (unlimited)', true);
                }
            }

            // recheck body size, we might had to read the whole body, so we abort late or trim here
            if($this->max_bodysize){
                if(strlen($r_body) > $this->max_bodysize){
                    if ($this->max_bodysize_abort) {
                        throw new HTTPClientException('Allowed response size exceeded');
                    } else {
                        $this->error = 'Allowed response size exceeded';
                    }
                }
            }

        } catch (HTTPClientException $err) {
            $this->error = $err->getMessage();
            if ($err->getCode())
                $this->status = $err->getCode();
            unset(self::$connections[$connectionId]);
            fclose($socket);
            return false;
        }

        if (!$this->keep_alive ||
                (isset($this->resp_headers['connection']) && $this->resp_headers['connection'] == 'Close')) {
            // close socket
            $status = socket_get_status($socket);
            fclose($socket);
            unset(self::$connections[$connectionId]);
        }

        // decode gzip if needed
        if(isset($this->resp_headers['content-encoding']) &&
           $this->resp_headers['content-encoding'] == 'gzip' &&
           strlen($r_body) > 10 && substr($r_body,0,3)=="\x1f\x8b\x08"){
            $this->resp_body = @gzinflate(substr($r_body, 10));
            if($this->resp_body === false){
                $this->error = 'Failed to decompress gzip encoded content';
                $this->resp_body = $r_body;
            }
        }else{
            $this->resp_body = $r_body;
        }

        $this->_debug('response body',$this->resp_body);
        $this->redirect_count = 0;
        return true;
    }

    /**
     * Tries to establish a CONNECT tunnel via Proxy
     *
     * Protocol, Servername and Port will be stripped from the request URL when a successful CONNECT happened
     *
     * @param resource &$socket
     * @param string   &$requesturl
     * @return bool true if a tunnel was established
     */
    function _ssltunnel(&$socket, &$requesturl){
        if(!$this->proxy_host) return false;
        $requestinfo = parse_url($requesturl);
        if($requestinfo['scheme'] != 'https') return false;
        if(!$requestinfo['port']) $requestinfo['port'] = 443;

        // build request
        $request  = "CONNECT {$requestinfo['host']}:{$requestinfo['port']} HTTP/1.0".HTTP_NL;
        $request .= "Host: {$requestinfo['host']}".HTTP_NL;
        if($this->proxy_user) {
                'Proxy-Authorization Basic '.base64_encode($this->proxy_user.':'.$this->proxy_pass).HTTP_NL;
        }
        $request .= HTTP_NL;

        $this->_debug('SSL Tunnel CONNECT',$request);
        $this->_sendData($socket, $request, 'SSL Tunnel CONNECT');

        // read headers from socket
        $r_headers = '';
        do{
            $r_line = $this->_readLine($socket, 'headers');
            $r_headers .= $r_line;
        }while($r_line != "\r\n" && $r_line != "\n");

        $this->_debug('SSL Tunnel Response',$r_headers);
        if(preg_match('/^HTTP\/1\.[01] 200/i',$r_headers)){
            if (stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_SSLv3_CLIENT)) {
                $requesturl = $requestinfo['path'];
                return true;
            }
        }
        return false;
    }

    /**
     * Safely write data to a socket
     *
     * @param  resource $socket     An open socket handle
     * @param  string   $data       The data to write
     * @param  string   $message    Description of what is being read
     * @throws HTTPClientException
     * @author Tom N Harris <tnharris@whoopdedo.org>
     */
    function _sendData($socket, $data, $message) {
        // send request
        $towrite = strlen($data);
        $written = 0;
        while($written < $towrite){
            // check timeout
            $time_used = $this->_time() - $this->start;
            if($time_used > $this->timeout)
                throw new HTTPClientException(sprintf('Timeout while sending %s (%.3fs)',$message, $time_used), -100);
            if(feof($socket))
                throw new HTTPClientException("Socket disconnected while writing $message");

            // select parameters
            $sel_r = null;
            $sel_w = array($socket);
            $sel_e = null;
            // wait for stream ready or timeout (1sec)
            if(@stream_select($sel_r,$sel_w,$sel_e,1) === false){
                 usleep(1000);
                 continue;
            }

            // write to stream
            $nbytes = fwrite($socket, substr($data,$written,4096));
            if($nbytes === false)
                throw new HTTPClientException("Failed writing to socket while sending $message", -100);
            $written += $nbytes;
        }
    }

    /**
     * Safely read data from a socket
     *
     * Reads up to a given number of bytes or throws an exception if the
     * response times out or ends prematurely.
     *
     * @param  resource $socket     An open socket handle in non-blocking mode
     * @param  int      $nbytes     Number of bytes to read
     * @param  string   $message    Description of what is being read
     * @param  bool     $ignore_eof End-of-file is not an error if this is set
     * @throws HTTPClientException
     * @return string
     * @author Tom N Harris <tnharris@whoopdedo.org>
     */
    function _readData($socket, $nbytes, $message, $ignore_eof = false) {
        $r_data = '';
        // Does not return immediately so timeout and eof can be checked
        if ($nbytes < 0) $nbytes = 0;
        $to_read = $nbytes;
        do {
            $time_used = $this->_time() - $this->start;
            if ($time_used > $this->timeout)
                throw new HTTPClientException(
                        sprintf('Timeout while reading %s after %d bytes (%.3fs)', $message,
                                strlen($r_data), $time_used), -100);
            if(feof($socket)) {
                if(!$ignore_eof)
                    throw new HTTPClientException("Premature End of File (socket) while reading $message");
                break;
            }

            if ($to_read > 0) {
                // select parameters
                $sel_r = array($socket);
                $sel_w = null;
                $sel_e = null;
                // wait for stream ready or timeout (1sec)
                if(@stream_select($sel_r,$sel_w,$sel_e,1) === false){
                     usleep(1000);
                     continue;
                }

                $bytes = fread($socket, $to_read);
                if($bytes === false)
                    throw new HTTPClientException("Failed reading from socket while reading $message", -100);
                $r_data .= $bytes;
                $to_read -= strlen($bytes);
            }
        } while ($to_read > 0 && strlen($r_data) < $nbytes);
        return $r_data;
    }

    /**
     * Safely read a \n-terminated line from a socket
     *
     * Always returns a complete line, including the terminating \n.
     *
     * @param  resource $socket     An open socket handle in non-blocking mode
     * @param  string   $message    Description of what is being read
     * @throws HTTPClientException
     * @return string
     * @author Tom N Harris <tnharris@whoopdedo.org>
     */
    function _readLine($socket, $message) {
        $r_data = '';
        do {
            $time_used = $this->_time() - $this->start;
            if ($time_used > $this->timeout)
                throw new HTTPClientException(
                        sprintf('Timeout while reading %s (%.3fs) >%s<', $message, $time_used, $r_data),
                        -100);
            if(feof($socket))
                throw new HTTPClientException("Premature End of File (socket) while reading $message");

            // select parameters
            $sel_r = array($socket);
            $sel_w = null;
            $sel_e = null;
            // wait for stream ready or timeout (1sec)
            if(@stream_select($sel_r,$sel_w,$sel_e,1) === false){
                 usleep(1000);
                 continue;
            }

            $r_data = fgets($socket, 1024);
        } while (!preg_match('/\n$/',$r_data));
        return $r_data;
    }

    /**
     * print debug info
     *
     * Uses _debug_text or _debug_html depending on the SAPI name
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    function _debug($info,$var=null){
        if(!$this->debug) return;
        if(php_sapi_name() == 'cli'){
            $this->_debug_text($info, $var);
        }else{
            $this->_debug_html($info, $var);
        }
    }

    /**
     * print debug info as HTML
     *
     * @param      $info
     * @param null $var
     */
    function _debug_html($info, $var=null){
        print '<b>'.$info.'</b> '.($this->_time() - $this->start).'s<br />';
        if(!is_null($var)){
            ob_start();
            print_r($var);
            $content = htmlspecialchars(ob_get_contents());
            ob_end_clean();
            print '<pre>'.$content.'</pre>';
        }
    }

    /**
     * prints debug info as plain text
     *
     * @param      $info
     * @param null $var
     */
    function _debug_text($info, $var=null){
        print '*'.$info.'* '.($this->_time() - $this->start)."s\n";
        if(!is_null($var)) print_r($var);
        print "\n-----------------------------------------------\n";
    }

    /**
     * Return current timestamp in microsecond resolution
     */
    static function _time(){
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }

    /**
     * convert given header string to Header array
     *
     * All Keys are lowercased.
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    function _parseHeaders($string){
        $headers = array();
        $lines = explode("\n",$string);
        array_shift($lines); //skip first line (status)
        foreach($lines as $line){
            @list($key, $val) = explode(':',$line,2);
            $key = trim($key);
            $val = trim($val);
            $key = strtolower($key);
            if(!$key) continue;
            if(isset($headers[$key])){
                if(is_array($headers[$key])){
                    $headers[$key][] = $val;
                }else{
                    $headers[$key] = array($headers[$key],$val);
                }
            }else{
                $headers[$key] = $val;
            }
        }
        return $headers;
    }

    /**
     * convert given header array to header string
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    function _buildHeaders($headers){
        $string = '';
        foreach($headers as $key => $value){
            if(empty($value)) continue;
            $string .= $key.': '.$value.HTTP_NL;
        }
        return $string;
    }

    /**
     * get cookies as http header string
     *
     * @author Andreas Goetz <cpuidle@gmx.de>
     */
    function _getCookies(){
        $headers = '';
        foreach ($this->cookies as $key => $val){
            $headers .= "$key=$val; ";
        }
        $headers = substr($headers, 0, -2);
        if ($headers) $headers = "Cookie: $headers".HTTP_NL;
        return $headers;
    }

    /**
     * Encode data for posting
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    function _postEncode($data){
        return http_build_query($data,'','&');
    }

    /**
     * Encode data for posting using multipart encoding
     *
     * @fixme use of urlencode might be wrong here
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    function _postMultipartEncode($data){
        $boundary = '--'.$this->boundary;
        $out = '';
        foreach($data as $key => $val){
            $out .= $boundary.HTTP_NL;
            if(!is_array($val)){
                $out .= 'Content-Disposition: form-data; name="'.urlencode($key).'"'.HTTP_NL;
                $out .= HTTP_NL; // end of headers
                $out .= $val;
                $out .= HTTP_NL;
            }else{
                $out .= 'Content-Disposition: form-data; name="'.urlencode($key).'"';
                if($val['filename']) $out .= '; filename="'.urlencode($val['filename']).'"';
                $out .= HTTP_NL;
                if($val['mimetype']) $out .= 'Content-Type: '.$val['mimetype'].HTTP_NL;
                $out .= HTTP_NL; // end of headers
                $out .= $val['body'];
                $out .= HTTP_NL;
            }
        }
        $out .= "$boundary--".HTTP_NL;
        return $out;
    }

    /**
     * Generates a unique identifier for a connection.
     *
     * @param  string $server
     * @param  string $port
     * @return string unique identifier
     */
    function _uniqueConnectionId($server, $port) {
        return "$server:$port";
    }
}

//Setup VIM: ex: et ts=4 :
