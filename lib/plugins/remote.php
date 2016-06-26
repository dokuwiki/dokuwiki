<?php

/**
 * Class DokuWiki_Remote_Plugin
 */
abstract class DokuWiki_Remote_Plugin extends DokuWiki_Plugin {

    private  $api;

    /**
     * Constructor
     */
    public function __construct() {
        $this->api = new RemoteAPI();
    }

    /**
     * Get all available methods with remote access.
     *
     * @abstract
     * @return array Information about all provided methods. {@see RemoteAPI}.
     */
    public abstract function _getMethods();

    /**
     * @return RemoteAPI
     */
    protected function getApi() {
        return $this->api;
    }

}
