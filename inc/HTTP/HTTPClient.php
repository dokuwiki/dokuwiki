<?php

namespace dokuwiki\HTTP;

define('HTTP_NL', "\r\n");


/**
 * This class implements a basic HTTP client
 *
 * It supports POST and GET, Proxy usage, basic authentication,
 * handles cookies and referrers. It is based upon the httpclient
 * function from the VideoDB project.
 *
 * @link   https://www.splitbrain.org/projects/videodb
 * @author Andreas Goetz <cpuidle@gmx.de>
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Tobias Sarnowski <sarnowski@new-thoughts.org>
 */
class HTTPClient
{
    //set these if you like
    public $agent;         // User agent
    public $http = '1.0';          // HTTP version defaults to 1.0
    public $timeout = 15;       // read timeout (seconds)
    public $cookies = [];
    public $referer = '';
    public $max_redirect = 3;
    public $max_bodysize = 0;
    public $max_bodysize_abort = true;  // if set, abort if the response body is bigger than max_bodysize
    public $header_regexp = ''; // if set this RE must match against the headers, else abort
    public $headers = [];
    public $debug = false;
    public $start = 0.0; // for timings
    public $keep_alive = true; // keep alive rocks

    // don't set these, read on error
    public $error;
    public $redirect_count = 0;

    // read these after a successful request
    public $status = 0;
    public $resp_body;
    public $resp_headers;

    // set these to do basic authentication
    public $user;
    public $pass;

    // set these if you need to use a proxy
    public $proxy_host;
    public $proxy_port;
    public $proxy_user;
    public $proxy_pass;
    public $proxy_ssl; //boolean set to true if your proxy needs SSL
    public $proxy_except; // regexp of URLs to exclude from proxy

    // list of kept alive connections
    protected static $connections = [];

    // what we use as boundary on multipart/form-data posts
    protected $boundary = '---DokuWikiHTTPClient--4523452351';

    /**
     * Constructor.
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    public function __construct()
    {
        $this->agent = 'Mozilla/4.0 (compatible; DokuWiki HTTP Client; ' . PHP_OS . ')';
        if (extension_loaded('zlib')) $this->headers['Accept-encoding'] = 'gzip';
        $this->headers['Accept'] = 'text/xml,application/xml,application/xhtml+xml,' .
            'text/html,text/plain,image/png,image/jpeg,image/gif,*/*';
        $this->headers['Accept-Language'] = 'en-us';
    }


    /**
     * Simple function to do a GET request
     *
     * Returns the wanted page or false on an error;
     *
     * @param string $url The URL to fetch
     * @param bool $sloppy304 Return body on 304 not modified
     * @return false|string  response body, false on error
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    public function get($url, $sloppy304 = false)
    {
        if (!$this->sendRequest($url)) return false;
        if ($this->status == 304 && $sloppy304) return $this->resp_body;
        if ($this->status < 200 || $this->status > 206) return false;
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
     * @param string $url The URL to fetch
     * @param array $data Associative array of parameters
     * @param bool $sloppy304 Return body on 304 not modified
     * @return false|string  response body, false on error
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    public function dget($url, $data, $sloppy304 = false)
    {
        if (strpos($url, '?')) {
            $url .= '&';
        } else {
            $url .= '?';
        }
        $url .= $this->postEncode($data);
        return $this->get($url, $sloppy304);
    }

    /**
     * Simple function to do a POST request
     *
     * Returns the resulting page or false on an error;
     *
     * @param string $url The URL to fetch
     * @param array $data Associative array of parameters
     * @return false|string  response body, false on error
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    public function post($url, $data)
    {
        if (!$this->sendRequest($url, $data, 'POST')) return false;
        if ($this->status < 200 || $this->status > 206) return false;
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
     * @param string $url - the complete URL
     * @param mixed $data - the post data either as array or raw data
     * @param string $method - HTTP Method usually GET or POST.
     * @return bool - true on success
     *
     * @author Andreas Goetz <cpuidle@gmx.de>
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    public function sendRequest($url, $data = '', $method = 'GET')
    {
        $this->start = microtime(true);
        $this->error = '';
        $this->status = 0;
        $this->resp_body = '';
        $this->resp_headers = [];

        // save unencoded data for recursive call
        $unencodedData = $data;

        // don't accept gzip if truncated bodies might occur
        if (
            $this->max_bodysize &&
            !$this->max_bodysize_abort &&
            isset($this->headers['Accept-encoding']) &&
            $this->headers['Accept-encoding'] == 'gzip'
        ) {
            unset($this->headers['Accept-encoding']);
        }

        // parse URL into bits
        $uri = parse_url($url);
        $server = $uri['host'];
        $path = empty($uri['path']) ? '/' : $uri['path'];
        $uriPort = empty($uri['port']) ? null : $uri['port'];
        if (!empty($uri['query'])) $path .= '?' . $uri['query'];
        if (isset($uri['user'])) $this->user = $uri['user'];
        if (isset($uri['pass'])) $this->pass = $uri['pass'];

        // proxy setup
        if ($this->useProxyForUrl($url)) {
            $request_url = $url;
            $server = $this->proxy_host;
            $port = $this->proxy_port;
            if (empty($port)) $port = 8080;
            $use_tls = $this->proxy_ssl;
        } else {
            $request_url = $path;
            $port = $uriPort ?: ($uri['scheme'] == 'https' ? 443 : 80);
            $use_tls = ($uri['scheme'] == 'https');
        }

        // add SSL stream prefix if needed - needs SSL support in PHP
        if ($use_tls) {
            if (!in_array('ssl', stream_get_transports())) {
                $this->status = -200;
                $this->error = 'This PHP version does not support SSL - cannot connect to server';
            }
            $server = 'ssl://' . $server;
        }

        // prepare headers
        $headers = $this->headers;
        $headers['Host'] = $uri['host']
            . ($uriPort ? ':' . $uriPort : '');
        $headers['User-Agent'] = $this->agent;
        $headers['Referer'] = $this->referer;

        if ($method == 'POST') {
            if (is_array($data)) {
                if (empty($headers['Content-Type'])) {
                    $headers['Content-Type'] = null;
                }
                if ($headers['Content-Type'] == 'multipart/form-data') {
                    $headers['Content-Type'] = 'multipart/form-data; boundary=' . $this->boundary;
                    $data = $this->postMultipartEncode($data);
                } else {
                    $headers['Content-Type'] = 'application/x-www-form-urlencoded';
                    $data = $this->postEncode($data);
                }
            }
        } elseif ($method == 'GET') {
            $data = ''; //no data allowed on GET requests
        }

        $contentlength = strlen($data);
        if ($contentlength) {
            $headers['Content-Length'] = $contentlength;
        }

        if ($this->user) {
            $headers['Authorization'] = 'Basic ' . base64_encode($this->user . ':' . $this->pass);
        }
        if ($this->proxy_user) {
            $headers['Proxy-Authorization'] = 'Basic ' . base64_encode($this->proxy_user . ':' . $this->proxy_pass);
        }

        // already connected?
        $connectionId = $this->uniqueConnectionId($server, $port);
        $this->debug('connection pool', self::$connections);
        $socket = null;
        if (isset(self::$connections[$connectionId])) {
            $this->debug('reusing connection', $connectionId);
            $socket = self::$connections[$connectionId];
        }
        if (is_null($socket) || feof($socket)) {
            $this->debug('opening connection', $connectionId);
            // open socket
            $socket = @fsockopen($server, $port, $errno, $errstr, $this->timeout);
            if (!$socket) {
                $this->status = -100;
                $this->error = "Could not connect to $server:$port\n$errstr ($errno)";
                return false;
            }

            // try to establish a CONNECT tunnel for SSL
            try {
                if ($this->ssltunnel($socket, $request_url)) {
                    // no keep alive for tunnels
                    $this->keep_alive = false;
                    // tunnel is authed already
                    if (isset($headers['Proxy-Authentication'])) unset($headers['Proxy-Authentication']);
                }
            } catch (HTTPClientException $e) {
                $this->status = $e->getCode();
                $this->error = $e->getMessage();
                fclose($socket);
                return false;
            }

            // keep alive?
            if ($this->keep_alive) {
                self::$connections[$connectionId] = $socket;
            } else {
                unset(self::$connections[$connectionId]);
            }
        }

        if ($this->keep_alive && !$this->useProxyForUrl($request_url)) {
            // RFC 2068, section 19.7.1: A client MUST NOT send the Keep-Alive
            // connection token to a proxy server. We still do keep the connection the
            // proxy alive (well except for CONNECT tunnels)
            $headers['Connection'] = 'Keep-Alive';
        } else {
            $headers['Connection'] = 'Close';
        }

        try {
            //set non-blocking
            stream_set_blocking($socket, 0);

            // build request
            $request = "$method $request_url HTTP/" . $this->http . HTTP_NL;
            $request .= $this->buildHeaders($headers);
            $request .= $this->getCookies();
            $request .= HTTP_NL;
            $request .= $data;

            $this->debug('request', $request);
            $this->sendData($socket, $request, 'request');

            // read headers from socket
            $r_headers = '';
            do {
                $r_line = $this->readLine($socket, 'headers');
                $r_headers .= $r_line;
            } while ($r_line != "\r\n" && $r_line != "\n");

            $this->debug('response headers', $r_headers);

            // check if expected body size exceeds allowance
            if ($this->max_bodysize && preg_match('/\r?\nContent-Length:\s*(\d+)\r?\n/i', $r_headers, $match)) {
                if ($match[1] > $this->max_bodysize) {
                    if ($this->max_bodysize_abort)
                        throw new HTTPClientException('Reported content length exceeds allowed response size');
                    else $this->error = 'Reported content length exceeds allowed response size';
                }
            }

            // get Status
            if (!preg_match('/^HTTP\/(\d\.\d)\s*(\d+).*?\n/s', $r_headers, $m))
                throw new HTTPClientException('Server returned bad answer ' . $r_headers);

            $this->status = $m[2];

            // handle headers and cookies
            $this->resp_headers = $this->parseHeaders($r_headers);
            if (isset($this->resp_headers['set-cookie'])) {
                foreach ((array)$this->resp_headers['set-cookie'] as $cookie) {
                    [$cookie] = sexplode(';', $cookie, 2, '');
                    [$key, $val] = sexplode('=', $cookie, 2, '');
                    $key = trim($key);
                    if ($val == 'deleted') {
                        if (isset($this->cookies[$key])) {
                            unset($this->cookies[$key]);
                        }
                    } elseif ($key) {
                        $this->cookies[$key] = $val;
                    }
                }
            }

            $this->debug('Object headers', $this->resp_headers);

            // check server status code to follow redirect
            if (in_array($this->status, [301, 302, 303, 307, 308])) {
                if (empty($this->resp_headers['location'])) {
                    throw new HTTPClientException('Redirect but no Location Header found');
                } elseif ($this->redirect_count == $this->max_redirect) {
                    throw new HTTPClientException('Maximum number of redirects exceeded');
                } else {
                    // close the connection because we don't handle content retrieval here
                    // that's the easiest way to clean up the connection
                    fclose($socket);
                    unset(self::$connections[$connectionId]);

                    $this->redirect_count++;
                    $this->referer = $url;
                    // handle non-RFC-compliant relative redirects
                    if (!preg_match('/^http/i', $this->resp_headers['location'])) {
                        if ($this->resp_headers['location'][0] != '/') {
                            $this->resp_headers['location'] = $uri['scheme'] . '://' . $uri['host'] . ':' . $uriPort .
                                dirname($path) . '/' . $this->resp_headers['location'];
                        } else {
                            $this->resp_headers['location'] = $uri['scheme'] . '://' . $uri['host'] . ':' . $uriPort .
                                $this->resp_headers['location'];
                        }
                    }
                    if ($this->status == 307 || $this->status == 308) {
                        // perform redirected request, same method as before (required by RFC)
                        return $this->sendRequest($this->resp_headers['location'], $unencodedData, $method);
                    } else {
                        // perform redirected request, always via GET (required by RFC)
                        return $this->sendRequest($this->resp_headers['location'], [], 'GET');
                    }
                }
            }

            // check if headers are as expected
            if ($this->header_regexp && !preg_match($this->header_regexp, $r_headers))
                throw new HTTPClientException('The received headers did not match the given regexp');

            //read body (with chunked encoding if needed)
            $r_body = '';
            if (
                (
                    isset($this->resp_headers['transfer-encoding']) &&
                    $this->resp_headers['transfer-encoding'] == 'chunked'
                ) || (
                    isset($this->resp_headers['transfer-coding']) &&
                    $this->resp_headers['transfer-coding'] == 'chunked'
                )
            ) {
                $abort = false;
                do {
                    $chunk_size = '';
                    while (preg_match('/^[a-zA-Z0-9]?$/', $byte = $this->readData($socket, 1, 'chunk'))) {
                        // read chunksize until \r
                        $chunk_size .= $byte;
                        if (strlen($chunk_size) > 128) // set an abritrary limit on the size of chunks
                            throw new HTTPClientException('Allowed response size exceeded');
                    }
                    $this->readLine($socket, 'chunk');     // readtrailing \n
                    $chunk_size = hexdec($chunk_size);

                    if ($this->max_bodysize && $chunk_size + strlen($r_body) > $this->max_bodysize) {
                        if ($this->max_bodysize_abort)
                            throw new HTTPClientException('Allowed response size exceeded');
                        $this->error = 'Allowed response size exceeded';
                        $chunk_size = $this->max_bodysize - strlen($r_body);
                        $abort = true;
                    }

                    if ($chunk_size > 0) {
                        $r_body .= $this->readData($socket, $chunk_size, 'chunk');
                        $this->readData($socket, 2, 'chunk'); // read trailing \r\n
                    }
                } while ($chunk_size && !$abort);
            } elseif (
                isset($this->resp_headers['content-length']) &&
                !isset($this->resp_headers['transfer-encoding'])
            ) {
                /* RFC 2616
                 * If a message is received with both a Transfer-Encoding header field and a Content-Length
                 * header field, the latter MUST be ignored.
                 */

                // read up to the content-length or max_bodysize
                // for keep alive we need to read the whole message to clean up the socket for the next read
                if (
                    !$this->keep_alive &&
                    $this->max_bodysize &&
                    $this->max_bodysize < $this->resp_headers['content-length']
                ) {
                    $length = $this->max_bodysize + 1;
                } else {
                    $length = $this->resp_headers['content-length'];
                }

                $r_body = $this->readData($socket, $length, 'response (content-length limited)', true);
            } elseif (!isset($this->resp_headers['transfer-encoding']) && $this->max_bodysize && !$this->keep_alive) {
                $r_body = $this->readData($socket, $this->max_bodysize + 1, 'response (content-length limited)', true);
            } elseif ((int)$this->status === 204) {
                // request has no content
            } else {
                // read entire socket
                while (!feof($socket)) {
                    $r_body .= $this->readData($socket, 4096, 'response (unlimited)', true);
                }
            }

            // recheck body size, we might have read max_bodysize+1 or even the whole body, so we abort late here
            if ($this->max_bodysize) {
                if (strlen($r_body) > $this->max_bodysize) {
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

        if (
            !$this->keep_alive ||
            (isset($this->resp_headers['connection']) && $this->resp_headers['connection'] == 'Close')
        ) {
            // close socket
            fclose($socket);
            unset(self::$connections[$connectionId]);
        }

        // decode gzip if needed
        if (
            isset($this->resp_headers['content-encoding']) &&
            $this->resp_headers['content-encoding'] == 'gzip' &&
            strlen($r_body) > 10 && str_starts_with($r_body, "\x1f\x8b\x08")
        ) {
            $this->resp_body = @gzinflate(substr($r_body, 10));
            if ($this->resp_body === false) {
                $this->error = 'Failed to decompress gzip encoded content';
                $this->resp_body = $r_body;
            }
        } else {
            $this->resp_body = $r_body;
        }

        $this->debug('response body', $this->resp_body);
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
     * @throws HTTPClientException when a tunnel is needed but could not be established
     */
    protected function ssltunnel(&$socket, &$requesturl)
    {
        if (!$this->useProxyForUrl($requesturl)) return false;
        $requestinfo = parse_url($requesturl);
        if ($requestinfo['scheme'] != 'https') return false;
        if (empty($requestinfo['port'])) $requestinfo['port'] = 443;

        // build request
        $request = "CONNECT {$requestinfo['host']}:{$requestinfo['port']} HTTP/1.0" . HTTP_NL;
        $request .= "Host: {$requestinfo['host']}" . HTTP_NL;
        if ($this->proxy_user) {
            $request .= 'Proxy-Authorization: Basic ' .
                base64_encode($this->proxy_user . ':' . $this->proxy_pass) . HTTP_NL;
        }
        $request .= HTTP_NL;

        $this->debug('SSL Tunnel CONNECT', $request);
        $this->sendData($socket, $request, 'SSL Tunnel CONNECT');

        // read headers from socket
        $r_headers = '';
        do {
            $r_line = $this->readLine($socket, 'headers');
            $r_headers .= $r_line;
        } while ($r_line != "\r\n" && $r_line != "\n");

        $this->debug('SSL Tunnel Response', $r_headers);
        if (preg_match('/^HTTP\/1\.[01] 200/i', $r_headers)) {
            // set correct peer name for verification (enabled since PHP 5.6)
            stream_context_set_option($socket, 'ssl', 'peer_name', $requestinfo['host']);

            // SSLv3 is broken, use only TLS connections.
            // @link https://bugs.php.net/69195
            if (PHP_VERSION_ID >= 50600 && PHP_VERSION_ID <= 50606) {
                $cryptoMethod = STREAM_CRYPTO_METHOD_TLS_CLIENT;
            } else {
                // actually means neither SSLv2 nor SSLv3
                $cryptoMethod = STREAM_CRYPTO_METHOD_SSLv23_CLIENT;
            }

            if (@stream_socket_enable_crypto($socket, true, $cryptoMethod)) {
                $requesturl = ($requestinfo['path'] ?? '/') .
                    (empty($requestinfo['query']) ? '' : '?' . $requestinfo['query']);
                return true;
            }

            throw new HTTPClientException(
                'Failed to set up crypto for secure connection to ' . $requestinfo['host'],
                -151
            );
        }

        throw new HTTPClientException('Failed to establish secure proxy connection', -150);
    }

    /**
     * Safely write data to a socket
     *
     * @param resource $socket An open socket handle
     * @param string $data The data to write
     * @param string $message Description of what is being read
     * @throws HTTPClientException
     *
     * @author Tom N Harris <tnharris@whoopdedo.org>
     */
    protected function sendData($socket, $data, $message)
    {
        // send request
        $towrite = strlen($data);
        $written = 0;
        while ($written < $towrite) {
            // check timeout
            $time_used = microtime(true) - $this->start;
            if ($time_used > $this->timeout)
                throw new HTTPClientException(sprintf('Timeout while sending %s (%.3fs)', $message, $time_used), -100);
            if (feof($socket))
                throw new HTTPClientException("Socket disconnected while writing $message");

            // select parameters
            $sel_r = null;
            $sel_w = [$socket];
            $sel_e = null;
            // wait for stream ready or timeout (1sec)
            if (@stream_select($sel_r, $sel_w, $sel_e, 1) === false) {
                usleep(1000);
                continue;
            }

            // write to stream
            $nbytes = fwrite($socket, substr($data, $written, 4096));
            if ($nbytes === false)
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
     * @param resource $socket An open socket handle in non-blocking mode
     * @param int $nbytes Number of bytes to read
     * @param string $message Description of what is being read
     * @param bool $ignore_eof End-of-file is not an error if this is set
     * @return string
     *
     * @throws HTTPClientException
     * @author Tom N Harris <tnharris@whoopdedo.org>
     */
    protected function readData($socket, $nbytes, $message, $ignore_eof = false)
    {
        $r_data = '';
        // Does not return immediately so timeout and eof can be checked
        if ($nbytes < 0) $nbytes = 0;
        $to_read = $nbytes;
        do {
            $time_used = microtime(true) - $this->start;
            if ($time_used > $this->timeout)
                throw new HTTPClientException(
                    sprintf(
                        'Timeout while reading %s after %d bytes (%.3fs)',
                        $message,
                        strlen($r_data),
                        $time_used
                    ),
                    -100
                );
            if (feof($socket)) {
                if (!$ignore_eof)
                    throw new HTTPClientException("Premature End of File (socket) while reading $message");
                break;
            }

            if ($to_read > 0) {
                // select parameters
                $sel_r = [$socket];
                $sel_w = null;
                $sel_e = null;
                // wait for stream ready or timeout (1sec)
                if (@stream_select($sel_r, $sel_w, $sel_e, 1) === false) {
                    usleep(1000);
                    continue;
                }

                $bytes = fread($socket, $to_read);
                if ($bytes === false)
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
     * @param resource $socket An open socket handle in non-blocking mode
     * @param string $message Description of what is being read
     * @return string
     *
     * @throws HTTPClientException
     * @author Tom N Harris <tnharris@whoopdedo.org>
     */
    protected function readLine($socket, $message)
    {
        $r_data = '';
        do {
            $time_used = microtime(true) - $this->start;
            if ($time_used > $this->timeout)
                throw new HTTPClientException(
                    sprintf('Timeout while reading %s (%.3fs) >%s<', $message, $time_used, $r_data),
                    -100
                );
            if (feof($socket))
                throw new HTTPClientException("Premature End of File (socket) while reading $message");

            // select parameters
            $sel_r = [$socket];
            $sel_w = null;
            $sel_e = null;
            // wait for stream ready or timeout (1sec)
            if (@stream_select($sel_r, $sel_w, $sel_e, 1) === false) {
                usleep(1000);
                continue;
            }

            $r_data = fgets($socket, 1024);
        } while (!preg_match('/\n$/', $r_data));
        return $r_data;
    }

    /**
     * print debug info
     *
     * Uses _debug_text or _debug_html depending on the SAPI name
     *
     * @param string $info
     * @param mixed $var
     * @author Andreas Gohr <andi@splitbrain.org>
     *
     */
    protected function debug($info, $var = null)
    {
        if (!$this->debug) return;
        if (PHP_SAPI == 'cli') {
            $this->debugText($info, $var);
        } else {
            $this->debugHtml($info, $var);
        }
    }

    /**
     * print debug info as HTML
     *
     * @param string $info
     * @param mixed $var
     */
    protected function debugHtml($info, $var = null)
    {
        echo '<b>' . $info . '</b> ' . (microtime(true) - $this->start) . 's<br />';
        if (!is_null($var)) {
            ob_start();
            print_r($var);
            $content = htmlspecialchars(ob_get_contents());
            ob_end_clean();
            echo '<pre>' . $content . '</pre>';
        }
    }

    /**
     * prints debug info as plain text
     *
     * @param string $info
     * @param mixed $var
     */
    protected function debugText($info, $var = null)
    {
        echo '*' . $info . '* ' . (microtime(true) - $this->start) . "s\n";
        if (!is_null($var)) print_r($var);
        echo "\n-----------------------------------------------\n";
    }

    /**
     * convert given header string to Header array
     *
     * All Keys are lowercased.
     *
     * @param string $string
     * @return array
     * @author Andreas Gohr <andi@splitbrain.org>
     *
     */
    protected function parseHeaders($string)
    {
        $headers = [];
        $lines = explode("\n", $string);
        array_shift($lines); //skip first line (status)
        foreach ($lines as $line) {
            [$key, $val] = sexplode(':', $line, 2, '');
            $key = trim($key);
            $val = trim($val);
            $key = strtolower($key);
            if (!$key) continue;
            if (isset($headers[$key])) {
                if (is_array($headers[$key])) {
                    $headers[$key][] = $val;
                } else {
                    $headers[$key] = [$headers[$key], $val];
                }
            } else {
                $headers[$key] = $val;
            }
        }
        return $headers;
    }

    /**
     * convert given header array to header string
     *
     * @param array $headers
     * @return string
     * @author Andreas Gohr <andi@splitbrain.org>
     *
     */
    protected function buildHeaders($headers)
    {
        $string = '';
        foreach ($headers as $key => $value) {
            if ($value === '') continue;
            $string .= $key . ': ' . $value . HTTP_NL;
        }
        return $string;
    }

    /**
     * get cookies as http header string
     *
     * @return string
     * @author Andreas Goetz <cpuidle@gmx.de>
     *
     */
    protected function getCookies()
    {
        $headers = '';
        foreach ($this->cookies as $key => $val) {
            $headers .= "$key=$val; ";
        }
        $headers = substr($headers, 0, -2);
        if ($headers) $headers = "Cookie: $headers" . HTTP_NL;
        return $headers;
    }

    /**
     * Encode data for posting
     *
     * @param array $data
     * @return string
     * @author Andreas Gohr <andi@splitbrain.org>
     *
     */
    protected function postEncode($data)
    {
        return http_build_query($data, '', '&');
    }

    /**
     * Encode data for posting using multipart encoding
     *
     * @fixme use of urlencode might be wrong here
     * @param array $data
     * @return string
     * @author Andreas Gohr <andi@splitbrain.org>
     *
     */
    protected function postMultipartEncode($data)
    {
        $boundary = '--' . $this->boundary;
        $out = '';
        foreach ($data as $key => $val) {
            $out .= $boundary . HTTP_NL;
            if (!is_array($val)) {
                $out .= 'Content-Disposition: form-data; name="' . urlencode($key) . '"' . HTTP_NL;
                $out .= HTTP_NL; // end of headers
                $out .= $val;
                $out .= HTTP_NL;
            } else {
                $out .= 'Content-Disposition: form-data; name="' . urlencode($key) . '"';
                if ($val['filename']) $out .= '; filename="' . urlencode($val['filename']) . '"';
                $out .= HTTP_NL;
                if ($val['mimetype']) $out .= 'Content-Type: ' . $val['mimetype'] . HTTP_NL;
                $out .= HTTP_NL; // end of headers
                $out .= $val['body'];
                $out .= HTTP_NL;
            }
        }
        $out .= "$boundary--" . HTTP_NL;
        return $out;
    }

    /**
     * Generates a unique identifier for a connection.
     *
     * @param string $server
     * @param string $port
     * @return string unique identifier
     */
    protected function uniqueConnectionId($server, $port)
    {
        return "$server:$port";
    }

    /**
     * Should the Proxy be used for the given URL?
     *
     * Checks the exceptions
     *
     * @param string $url
     * @return bool
     */
    protected function useProxyForUrl($url)
    {
        return $this->proxy_host && (!$this->proxy_except || !preg_match('/' . $this->proxy_except . '/i', $url));
    }
}
