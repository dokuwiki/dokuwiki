<?php

abstract class DokuWiki_Remote_Plugin extends DokuWiki_Plugin {

    /**
     * @abstract
     * @return array Information to all provided methods. {@see RemoteAPI}.
     */
    public abstract function _getMethods();

}
