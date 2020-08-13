<?php

namespace dokuwiki\Menu;

/**
 * Class PageMenu
 *
 * Actions manipulating the current page. Shown as a floating menu in the dokuwiki template
 */
class PageMenu extends AbstractMenu {

    protected $view = 'page';

    protected $types = array(
        'Edit',
        'Revert',
        'Revisions',
        'Backlink',
        'Subscribe',
        'Top',
    );

}
