<?php

namespace easywiki\Menu\Item;

/**
 * Class Top
 *
 * Scroll back to the top. Uses a hash as $id which is handled special in getLink().
 * Not shown in mobile context
 */
class Top extends AbstractItem
{
    /** @inheritdoc */
    public function __construct()
    {
        parent::__construct();

        $this->svg = WIKI_INC . 'lib/images/menu/10-top_arrow-up.svg';
        $this->accesskey = 't';
        $this->params = ['do' => ''];
        $this->id = '#easywiki__top';
        $this->context = self::CTX_DESKTOP;
    }

    /**
     * Convenience method to create a <button> element
     *
     * Uses html_topbtn()
     *
     * @return string
     * @todo this does currently not support the SVG icon
     */
    public function asHtmlButton()
    {
        return html_topbtn();
    }
}
