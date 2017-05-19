<?php

namespace dokuwiki\Menu;

/**
 * Class SiteMenu
 *
 * Actions that are not bound to an individual page but provide toolsfor the whole wiki.
 */
class SiteMenu extends AbstractMenu {

    protected $view = 'site';

    protected $types = array(
        'Recent',
        'Media',
        'Index'
    );

}
