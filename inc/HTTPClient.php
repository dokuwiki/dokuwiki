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
    function DokuHTTPClient(){
        global $conf;

        // call parent constructor
        $this->HTTPClient();

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
    function HTTPClient(){
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
        if(isset($uri['port']) && !empty($uri['port'])) $port = $uri['port'];
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
        if($uri['port']) $headers['Host'].= ':'.$uri['port'];
        $headers['User-Agent'] = $this->agent;
        $headers['Referer']    = $this->referer;
        if ($this->keep_alive) {
            $headers['Connection'] = 'Keep-Alive';
        } else {
            $headers['Connection'] = 'Close';
        }
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

        // stop time
        $start = time();

        // already connected?
        $connectionId = $this->_uniqueConnectionId($server,$port);
        $this->_debug('connection pool', $this->connections);
        $socket = null;
        if (isset($this->connections[$connectionId])) {
            $this->_debug('reusing connection', $connectionId);
            $socket = $this->connections[$connectionId];
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

            // keep alive?
            if ($this->keep_alive) {
                $this->connections[$connectionId] = $socket;
            } else {
                unset($this->connections[$connectionId]);
            }
        }

        //set blocking
        stream_set_blocking($socket,1);

        // build request
        $request  = "$method $request_url HTTP/".$this->http.HTTP_NL;
        $request .= $this->_buildHeaders($headers);
        $request .= $this->_getCookies();
        $request .= HTTP_NL;
        $request .= $data;

        $this->_debug('request',$request);

        // select parameters
        $sel_r = null;
        $sel_w = array($socket);
        $sel_e = null;

        // send request
        $towrite = strlen($request);
        $written = 0;
        while($written < $towrite){
            // check timeout
            if(time()-$start > $this->timeout){
                $this->status = -100;
                $this->error = sprintf('Timeout while sending request (%.3fs)',$this->_time() - $this->start);
                unset($this->connections[$connectionId]);
                return false;
            }

            // wait for stream ready or timeout (1sec)
            if(stream_select($sel_r,$sel_w,$sel_e,1) === false) continue;

            // write to stream
            $ret = fwrite($socket, substr($request,$written,4096));
            if($ret === false){
                $this->status = -100;
                $this->error = 'Failed writing to socket';
                unset($this->connections[$connectionId]);
                return false;
            }
            $written += $ret;
        }

        // continue non-blocking
        stream_set_blocking($socket,0);

        // read headers from socket
        $r_headers = '';
        do{
            if(time()-$start > $this->timeout){
                $this->status = -100;
                $this->error = sprintf('Timeout while reading headers (%.3fs)',$this->_time() - $this->start);
                unset($this->connections[$connectionId]);
                return false;
            }
            if(feof($socket)){
                $this->error = 'Premature End of File (socket)';
                unset($this->connections[$connectionId]);
                return false;
            }
            usleep(1000);
            $r_headers .= fgets($socket,1024);
        }while(!preg_match('/\r?\n\r?\n$/',$r_headers));

        $this->_debug('response headers',$r_headers);

        // check if expected body size exceeds allowance
        if($this->max_bodysize && preg_match('/\r?\nContent-Length:\s*(\d+)\r?\n/i',$r_headers,$match)){
            if($match[1] > $this->max_bodysize){
                $this->error = 'Reported content length exceeds allowed response size';
                if ($this->max_bodysize_abort)
                    unset($this->connections[$connectionId]);
                    return false;
            }
        }

        // get Status
        if (!preg_match('/^HTTP\/(\d\.\d)\s*(\d+).*?\n/', $r_headers, $m)) {
            $this->error = 'Server returned bad answer';
            unset($this->connections[$connectionId]);
            return false;
        }
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
            // close the connection because we don't handle content retrieval here
            // that's the easiest way to clean up the connection
            fclose($socket);
            unset($this->connections[$connectionId]);

            if (empty($this->resp_headers['location'])){
                $this->error = 'Redirect but no Location Header found';
                return false;
            }elseif($this->redirect_count == $this->max_redirect){
                $this->error = 'Maximum number of redirects exceeded';
                return false;
            }else{
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
        if($this->header_regexp && !preg_match($this->header_regexp,$r_headers)){
            $this->error = 'The received headers did not match the given regexp';
            unset($this->connections[$connectionId]);
            return false;
        }

        //read body (with chunked encoding if needed)
        $r_body    = '';
        if(preg_match('/transfer\-(en)?coding:\s*chunked\r\n/i',$r_headers)){
            do {
                unset($chunk_size);
                do {
                    if(feof($socket)){
                        $this->error = 'Premature End of File (socket)';
                        unset($this->connections[$connectionId]);
                        return false;
                    }
                    if(time()-$start > $this->timeout){
                        $this->status = -100;
                        $this->error = sprintf('Timeout while reading chunk (%.3fs)',$this->_time() - $this->start);
                        unset($this->connections[$connectionId]);
                        return false;
                    }
                    $byte = fread($socket,1);
                    $chunk_size .= $byte;
                } while (preg_match('/[a-zA-Z0-9]/',$byte)); // read chunksize including \r

                $byte = fread($socket,1);     // readtrailing \n
                $chunk_size = hexdec($chunk_size);
                if ($chunk_size) {
                    $this_chunk = fread($socket,$chunk_size);
                    $r_body    .= $this_chunk;
                    $byte = fread($socket,2); // read trailing \r\n
                }

                if($this->max_bodysize && strlen($r_body) > $this->max_bodysize){
                    $this->error = 'Allowed response size exceeded';
                    if ($this->max_bodysize_abort){
                        unset($this->connections[$connectionId]);
                        return false;
                    } else {
                        break;
                    }
                }
            } while ($chunk_size);
        }else{
            // read entire socket
            while (!feof($socket)) {
                if(time()-$start > $this->timeout){
                    $this->status = -100;
                    $this->error = sprintf('Timeout while reading response (%.3fs)',$this->_time() - $this->start);
                    unset($this->connections[$connectionId]);
                    return false;
                }
                $r_body .= fread($socket,4096);
                $r_size = strlen($r_body);
                if($this->max_bodysize && $r_size > $this->max_bodysize){
                    $this->error = 'Allowed response size exceeded';
                    if ($this->max_bodysize_abort) {
                        unset($this->connections[$connectionId]);
                        return false;
                    } else {
                        break;
                    }
                }
                if(isset($this->resp_headers['content-length']) &&
                   !isset($this->resp_headers['transfer-encoding']) &&
                   $this->resp_headers['content-length'] == $r_size){
                    // we read the content-length, finish here
                    break;
                }
            }
        }

        if (!$this->keep_alive ||
                (isset($this->resp_headers['connection']) && $this->resp_headers['connection'] == 'Close')) {
            // close socket
            $status = socket_get_status($socket);
            fclose($socket);
            unset($this->connections[$connectionId]);
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
     * print debug info
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    function _debug($info,$var=null){
        if(!$this->debug) return;
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
     * Return current timestamp in microsecond resolution
     */
    function _time(){
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
        if (!preg_match_all('/^\s*([\w-]+)\s*:\s*([\S \t]+)\s*$/m', $string,
                            $matches, PREG_SET_ORDER)) {
            return $headers;
        }
        foreach($matches as $match){
            list(, $key, $val) = $match;
            $key = strtolower($key);
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
        if ($headers !== '') $headers = "Cookie: $headers".HTTP_NL;
        return $headers;
    }

    /**
     * Encode data for posting
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    function _postEncode($data){
        $url = '';
        foreach($data as $key => $val){
            if($url) $url .= '&';
            $url .= urlencode($key).'='.urlencode($val);
        }
        return $url;
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
     * @return string unique identifier
     */
    function _uniqueConnectionId($server, $port) {
        return "$server:$port";
    }
}

//Setup VIM: ex: et ts=4 :
