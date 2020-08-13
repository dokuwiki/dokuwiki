<?php
/**
 * We override some methods of the original SimplePie class here
 */
class FeedParser extends SimplePie {

    /**
     * Constructor. Set some defaults
     */
    public function __construct(){
        parent::__construct();
        $this->enable_cache(false);
        $this->set_file_class(\dokuwiki\FeedParserFile::class);
    }

    /**
     * Backward compatibility for older plugins
     *
     * phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
     * @param string $url
     */
    public function feed_url($url){
        $this->set_feed_url($url);
    }
}


