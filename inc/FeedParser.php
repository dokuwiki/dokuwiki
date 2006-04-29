<?php
/**
 * Class used to parse RSS and ATOM feeds
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */

if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../').'/');
require_once(DOKU_INC.'inc/HTTPClient.php');
require_once(DOKU_INC.'inc/SimplePie.php');


/**
 * We override some methods of the original SimplePie class here
 */
class FeedParser extends SimplePie {

    /**
     * Constructor. Set some defaults
     */
    function FeedParser(){
        $this->SimplePie();
        $this->caching = false;
    }

    /**
     * Fetch an URL using our own HTTPClient
     *
     * Overrides SimplePie's own method
     */
    function get_file($url){
        $http = new DokuHTTPClient();
        return $http->get($url,true);
    }
}
