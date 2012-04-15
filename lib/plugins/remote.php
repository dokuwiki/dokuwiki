<?php

abstract class DokuWiki_Remote_Plugin extends DokuWiki_Plugin {

    private  $api;

    public function __construct() {
        $this->api = new RemoteAPI();
    }

    /**
     * @abstract
     * @return array Information to all provided methods. {@see RemoteAPI}.
     */
    public abstract function _getMethods();

    protected function getApi() {
        return $this->api;
    }

}
