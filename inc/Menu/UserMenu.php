<?php

namespace dokuwiki\Menu;

class UserMenu extends AbstractMenu {

    protected $view = 'user';

    protected $types = array(
        'Profile',
        'Admin',
        'Register',
        'Login',
    );

}
