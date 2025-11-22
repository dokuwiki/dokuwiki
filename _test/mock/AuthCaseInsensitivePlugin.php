<?php

namespace easywiki\test\mock;

/**
 * Class easywiki\Plugin\EasyWiki_Auth_Plugin
 */
class AuthCaseInsensitivePlugin extends AuthPlugin {
    function isCaseSensitive(){
        return false;
    }
}