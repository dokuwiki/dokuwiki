<?php

namespace dokuwiki\test\mock;

/**
 * Class dokuwiki\Plugin\DokuWiki_Auth_Plugin
 */
class AuthCaseInsensitivePlugin extends AuthPlugin {
    function isCaseSensitive(){
        return false;
    }
}