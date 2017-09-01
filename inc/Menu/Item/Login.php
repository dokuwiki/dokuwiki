<?php

namespace dokuwiki\Menu\Item;

/**
 * Class Login
 *
 * Show a login or logout item, based on the current state
 */
class Login extends AbstractItem {

    /** @inheritdoc */
    public function __construct() {
        global $INPUT;
        parent::__construct();

        $this->svg = DOKU_INC . 'lib/images/menu/login.svg';
        $this->params['sectok'] = getSecurityToken();
        if($INPUT->server->has('REMOTE_USER')) {
            if(!actionOK('logout')) {
                throw new \RuntimeException("logout disabled");
            }
            $this->params['do'] = 'logout';
            $this->type = 'logout';
            $this->svg = DOKU_INC . 'lib/images/menu/logout.svg';
        }
    }

}
