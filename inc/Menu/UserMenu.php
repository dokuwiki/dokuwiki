<?php

namespace dokuwiki\Menu;

/**
 * Class UserMenu
 *
 * Actions related to the current user
 */
class UserMenu extends AbstractMenu
{
    protected $view = 'user';

    protected $types = ['Profile', 'Admin', 'Register', 'Login'];
}
