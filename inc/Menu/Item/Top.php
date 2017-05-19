<?php

namespace dokuwiki\Menu\Item;

class Top extends AbstractItem {

    protected $svg       = DOKU_BASE . 'lib/images/menu/10-top_arrow-up.svg';
    protected $accesskey = 't';
    protected $params    = array('do' => '');
    protected $id        = '#dokuwiki__top';
    protected $context   = self::CTX_DESKTOP;

}
