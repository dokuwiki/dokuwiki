<?php

namespace dokuwiki\Menu;

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
