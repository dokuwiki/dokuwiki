<?php

namespace dokuwiki\Menu\Item;

/**
 * Class AbstractItem
 *
 * This class defines a single Item to be displayed in one of DokuWiki's menus. Plugins
 * can extend those menus through action plugins and add their own instances of this class,
 * overwriting some of its properties.
 *
 * Items may be shown multiple times in different contexts. Eg. for the default template
 * all menus are shown in a Dropdown list on mobile, but are split into several places on
 * desktop. The item's $context property can be used to hide the item depending on the current
 * context.
 *
 * Children usually just need to overwrite the different properties, but for complex things
 * the accessors may be overwritten instead.
 */
abstract class AbstractItem {

    /** menu item is to be shown on desktop screens only */
    const CTX_DESKTOP = 1;
    /** menu item is to be shown on mobile screens only */
    const CTX_MOBILE = 2;
    /** menu item is to be shown in all contexts */
    const CTX_ALL = 3;

    protected $type        = '';
    protected $accesskey   = '';
    protected $id          = '';
    protected $method      = 'get';
    protected $params      = array();
    protected $nofollow    = true;
    protected $replacement = '';
    protected $category    = 'page';
    protected $svg         = DOKU_INC . 'lib/images/menu/00-default_checkbox-blank-circle-outline.svg';
    protected $label       = '';
    protected $context     = self::CTX_ALL;

    public function __construct() {
        global $ID;
        $this->id = $ID;
        $this->type = strtolower(substr(strrchr(get_class($this), '\\'), 1));
        $this->params['do'] = $this->type;

        if(!actionOK($this->type)) throw new \RuntimeException("action disabled: {$this->type}");
    }

    /**
     * Return this item's label
     *
     * When the label property was set, it is simply returned. Otherwise, the action's type
     * is used to look up the translation in the main language file and, if used, the replacement
     * is applied.
     *
     * @return string
     */
    public function getLabel() {
        if($this->label !== '') return $this->label;

        /** @var array $lang */
        global $lang;
        $label = $lang['btn_' . $this->type];
        if(strpos($label, '%s')) {
            $label = sprintf($label, $this->replacement);
        }
        if($label === '') $label = '[' . $this->type . ']';
        return $label;
    }

    /**
     * Return the link this item links to
     *
     * Basically runs wl() on $id and $params. However if the ID is a hash it is used directly
     * as the link
     *
     * @see wl()
     * @return string
     */
    public function getLink() {
        if($this->id[0] == '#') {
            return $this->id;
        } else {
            return wl($this->id, $this->params);
        }
    }

    /**
     * Convenience method to get the attributes for constructing an <a> element
     *
     * @see buildAttributes()
     * @param string|false $classprefix create a class from type with this prefix, false for no class
     * @return array
     */
    public function getLinkAttributes($classprefix = 'menuitem ') {
        $attr = array(
            'href' => $this->getLink(),
            'title' => $this->getLabel(),
        );
        if($this->isNofollow()) $attr['rel'] = 'nofollow';
        if($this->getAccesskey()) {
            $attr['accesskey'] = $this->getAccesskey();
            $attr['title'] .= ' [' . $this->getAccesskey() . ']';
        }
        if($classprefix !== false) $attr['class'] = $classprefix . $this->getType();

        return $attr;
    }

    /**
     * Convenience method to create a full <a> element
     *
     * Wraps around the label and SVG image
     *
     * @param string|false $classprefix create a class from type with this prefix, false for no class
     * @return string
     */
    public function asHtmlLink($classprefix = 'menuitem ') {
        $attr = buildAttributes($this->getLinkAttributes($classprefix));
        $html = "<a $attr>";
        $html .= '<span>' . hsc($this->getLabel()) . '</span>';
        $html .= inlinSVG($this->getSvg());
        $html .= "</a>";

        return $html;
    }

    /**
     * Should this item be shown in the given context
     *
     * @param int $ctx the current context
     * @return bool
     */
    public function visibleInContext($ctx) {
        return (bool) ($ctx & $this->context);
    }

    /**
     * @return string the name of this item
     */
    public function getType() {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getAccesskey() {
        return $this->accesskey;
    }

    /**
     * @return array
     */
    public function getParams() {
        return $this->params;
    }

    /**
     * @return bool
     */
    public function isNofollow() {
        return $this->nofollow;
    }

    /**
     * @return string
     */
    public function getSvg() {
        return $this->svg;
    }

}
