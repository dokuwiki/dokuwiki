<?php

namespace dokuwiki\Menu;

class SiteMenu extends AbstractMenu {

    protected $view = 'site';

    protected $types = array(
        'Recent',
        'Media',
        'Index'
    );

}
