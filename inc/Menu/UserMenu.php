<?php

namespace dokuwiki\Menu;

class UserMenu extends AbstractMenu {

    protected $view = 'user';

    protected $types = array(
        'Login',
        'Register',
        'Profile',
        'Admin'
    );

}
