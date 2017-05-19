<?php

namespace dokuwiki\Menu;

use dokuwiki\Menu\Item\AbstractItem;

abstract class AbstractMenu {

    /** @var string[] list of Item classes to load */
    protected $types = array();

    /** @var int the context this menu is used in */
    protected $context = AbstractItem::CTX_DESKTOP;

    /** @var string view identifier to be set in the event */
    protected $view = '';

    /**
     * AbstractMenu constructor.
     *
     * @param int $context the context this menu is used in
     */
    public function __construct($context = AbstractItem::CTX_DESKTOP) {
        $this->context = $context;
    }

    /**
     * Get the list of action items in this menu
     *
     * @return AbstractItem[]
     * @triggers MENU_ITEMS_ASSEMBLY
     */
    public function getItems() {
        $data = array(
            'view' => $this->view,
            'items' => array(),
        );
        trigger_event('MENU_ITEMS_ASSEMBLY', $data, array($this, 'loadItems'));
        return $data['items'];
    }

    /**
     * Default action for the MENU_ITEMS_ASSEMBLY event
     *
     * @see getItems()
     * @param array $data The plugin data
     */
    public function loadItems(&$data) {
        foreach($this->types as $class) {
            try {
                $class = "\\dokuwiki\\Menu\\Item\\$class";
                /** @var AbstractItem $item */
                $item = new $class();
                if(!$item->visibleInContext($this->context)) continue;
                $data['items'][$item->getType()] = $item;
            } catch(\RuntimeException $ignored) {
                // item not available
            }
        }
    }

    /**
     * Generate HTML list items for this menu
     *
     * This is a convenience method for template authors. If you need more fine control over the
     * output, use getItems() and build the HTML yourself
     *
     * @param string|false $classprefix create a class from type with this prefix, false for no class
     * @return string
     */
    public function getListItems($classprefix = '') {
        $html = '';
        foreach($this->getItems() as $item) {
            if($classprefix !== false) {
                $class = ' class="' . $classprefix . $item->getType() . '"';
            } else {
                $class = '';
            }

            $html .= "<li$class>";
            $html .= $item->asHtmlLink(false);
            $html .= '</li>';
        }
        return $html;
    }

}
