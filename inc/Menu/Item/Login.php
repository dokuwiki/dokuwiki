<?php

namespace dokuwiki\Menu\Item;

class Login extends AbstractItem {

    /** @inheritdoc */
    public function __construct() {
        global $INPUT;
        parent::__construct();

        $this->category = 'user';
        $this->params['sectok'] = getSecurityToken();
        if($INPUT->server->has('REMOTE_USER')) {
            if(!actionOK('logout')) {
                throw new \RuntimeException("logout disabled");
            }
            $this->params['do'] = 'logout';
            $this->type = 'logout';
        }
    }

}
