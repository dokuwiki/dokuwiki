<?php

namespace dokuwiki\Menu\Item;

use dokuwiki\File\StaticImage;

/**
 * Class Login
 *
 * Show a login or logout item, based on the current state
 */
class Login extends AbstractItem
{
    /** @inheritdoc */
    public function __construct()
    {
        global $INPUT;
        parent::__construct();

        $this->svg = StaticImage::path('menu/login.svg');
        $this->params['sectok'] = getSecurityToken();
        if ($INPUT->server->has('REMOTE_USER')) {
            if (!actionOK('logout')) {
                throw new \RuntimeException("logout disabled");
            }
            $this->params['do'] = 'logout';
            $this->type = 'logout';
            $this->svg = StaticImage::path('menu/logout.svg');
        }
    }
}
