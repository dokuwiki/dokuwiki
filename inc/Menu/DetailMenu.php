<?php

namespace dokuwiki\Menu;

/**
 * Class DetailMenu
 *
 * This menu offers options on an image detail view. It usually displayed similar to
 * the PageMenu.
 */
class DetailMenu extends AbstractMenu {

    protected $view = 'detail';

    protected $types = array(
        'MediaManager',
        'ImgBackto',
        'Top',
    );

}
