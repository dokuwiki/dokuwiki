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

    /** @var string name of the action, usually the lowercase class name */
    protected $type = '';
    /** @var string optional keyboard shortcut */
    protected $accesskey = '';
    /** @var string the page id this action links to */
    protected $id = '';
    /** @var string the method to be used when this action is used in a form */
    protected $method = 'get';
    /** @var array parameters for the action (should contain the do parameter) */
    protected $params = array();
    /** @var bool when true, a rel=nofollow should be used */
    protected $nofollow = true;
    /** @var string this item's label may contain a placeholder, which is replaced with this */
    protected $replacement = '';
    /** @var string the full path to the SVG icon of this menu item */
    protected $svg = DOKU_INC . 'lib/images/menu/00-default_checkbox-blank-circle-outline.svg';
    /** @var string can be set to overwrite the default lookup in $lang.btn_* */
    protected $label = '';
    /** @var string the tooltip title, defaults to $label */
    protected $title = '';
    /** @var int the context this titme is shown in */
    protected $context = self::CTX_ALL;

    /**
     * AbstractItem constructor.
     *
     * Sets the dynamic properties
     *
     * Children should always call the parent constructor!
     *
     * @throws \RuntimeException when the action is disabled
     */
    public function __construct() {
        global $ID;
        $this->id = $ID;
        $this->type = $this->getType();
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
     * Return this item's title
     *
     * This title should be used to display a tooltip (using the HTML title attribute). If
     * a title property was not explicitly set, the label will be returned.
     *
     * @return string
     */
    public function getTitle() {
        if($this->title === '') return $this->getLabel();
        return $this->title;
    }

    /**
     * Return the link this item links to
     *
     * Basically runs wl() on $id and $params. However if the ID is a hash it is used directly
     * as the link
     *
     * Please note that the generated URL is *not* XML escaped.
     *
     * @see wl()
     * @return string
     */
    public function getLink() {
        if($this->id && $this->id[0] == '#') {
            return $this->id;
        } else {
            return wl($this->id, $this->params, false, '&');
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
            'title' => $this->getTitle(),
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
     * @param bool $svg add SVG icon to the link
     * @return string
     */
    public function asHtmlLink($classprefix = 'menuitem ', $svg = true) {
        $attr = buildAttributes($this->getLinkAttributes($classprefix));
        $html = "<a $attr>";
        if($svg) {
            $html .= '<span>' . hsc($this->getLabel()) . '</span>';
            $html .= inlineSVG($this->getSvg());
        } else {
            $html .= hsc($this->getLabel());
        }
        $html .= "</a>";

        return $html;
    }

    /**
     * Convenience method to create a <button> element inside it's own form element
     *
     * Uses html_btn()
     *
     * @return string
     */
    public function asHtmlButton() {
        return html_btn(
            $this->getType(),
            $this->id,
            $this->getAccesskey(),
            $this->getParams(),
            $this->method,
            $this->getTitle(),
            $this->getLabel(),
            $this->getSvg()
        );
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
        if($this->type === '') {
            $this->type = strtolower(substr(strrchr(get_class($this), '\\'), 1));
        }
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

    /**
     * Return this Item's settings as an array as used in tpl_get_action()
     *
     * @return array
     */
    public function getLegacyData() {
        return array(
            'accesskey' => $this->accesskey ?: null,
            'type' => $this->type,
            'id' => $this->id,
            'method' => $this->method,
            'params' => $this->params,
            'nofollow' => $this->nofollow,
            'replacement' => $this->replacement
        );
    }
}
