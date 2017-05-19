<?php

namespace dokuwiki\Menu\Item;

/**
 * Class Top
 *
 * Scroll back to the top. Uses a hash as $id which is handled special in getLink().
 * Not shown in mobile context
 */
class Top extends AbstractItem {

    protected $svg       = DOKU_INC . 'lib/images/menu/10-top_arrow-up.svg';
    protected $accesskey = 't';
    protected $params    = array('do' => '');
    protected $id        = '#dokuwiki__top';
    protected $context   = self::CTX_DESKTOP;

}
