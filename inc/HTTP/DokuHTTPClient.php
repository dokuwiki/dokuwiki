<?php


namespace dokuwiki\HTTP;



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
    public function __construct(){
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

        // allow enabling debugging via URL parameter (if debugging allowed)
        if($conf['allowdebug']) {
            if(
                isset($_REQUEST['httpdebug']) ||
                (
                    isset($_SERVER['HTTP_REFERER']) &&
                    strpos($_SERVER['HTTP_REFERER'], 'httpdebug') !== false
                )
            ) {
                $this->debug = true;
            }
        }
    }


    /**
     * Wraps an event around the parent function
     *
     * @triggers HTTPCLIENT_REQUEST_SEND
     * @author   Andreas Gohr <andi@splitbrain.org>
     */
    /**
     * @param string $url
     * @param string|array $data the post data either as array or raw data
     * @param string $method
     * @return bool
     */
    public function sendRequest($url,$data='',$method='GET'){
        $httpdata = array('url'    => $url,
            'data'   => $data,
            'method' => $method);
        $evt = new \Doku_Event('HTTPCLIENT_REQUEST_SEND',$httpdata);
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

