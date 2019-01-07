<?php

namespace dokuwiki\Menu;

use dokuwiki\Menu\Item\AbstractItem;

/**
 * Class MobileMenu
 *
 * Note: this does not inherit from AbstractMenu because it is not working like the other
 * menus. This is a meta menu, aggregating the items from the other menus and offering a combined
 * view. The idea is to use this on mobile devices, thus the context is fixed to CTX_MOBILE
 */
class MobileMenu implements MenuInterface {

    /**
     * Returns all items grouped by view
     *
     * @return AbstractItem[][]
     */
    public function getGroupedItems() {
        $pagemenu = new PageMenu(AbstractItem::CTX_MOBILE);
        $sitemenu = new SiteMenu(AbstractItem::CTX_MOBILE);
        $usermenu = new UserMenu(AbstractItem::CTX_MOBILE);

        return array(
            'page' => $pagemenu->getItems(),
            'site' => $sitemenu->getItems(),
            'user' => $usermenu->getItems()
        );
    }

    /**
     * Get all items in a flat array
     *
     * This returns the same format as AbstractMenu::getItems()
     *
     * @return AbstractItem[]
     */
    public function getItems() {
        $menu = $this->getGroupedItems();
        return call_user_func_array('array_merge', array_values($menu));
    }

    /**
     * Print a dropdown menu with all DokuWiki actions
     *
     * Note: this will not use any pretty URLs
     *
     * @param string $empty empty option label
     * @param string $button submit button label
     * @return string
     */
    public function getDropdown($empty = '', $button = '&gt;') {
        global $ID;
        global $REV;
        /** @var string[] $lang */
        global $lang;
        global $INPUT;

        $html = '<form action="' . script() . '" method="get" accept-charset="utf-8">';
        $html .= '<div class="no">';
        $html .= '<input type="hidden" name="id" value="' . $ID . '" />';
        if($REV) $html .= '<input type="hidden" name="rev" value="' . $REV . '" />';
        if($INPUT->server->str('REMOTE_USER')) {
            $html .= '<input type="hidden" name="sectok" value="' . getSecurityToken() . '" />';
        }

        $html .= '<select name="do" class="edit quickselect" title="' . $lang['tools'] . '">';
        $html .= '<option value="">' . $empty . '</option>';

        foreach($this->getGroupedItems() as $tools => $items) {
            if (count($items)) {
                $html .= '<optgroup label="' . $lang[$tools . '_tools'] . '">';
                foreach($items as $item) {
                    $params = $item->getParams();
                    $html .= '<option value="' . $params['do'] . '">';
                    $html .= hsc($item->getLabel());
                    $html .= '</option>';
                }
                $html .= '</optgroup>';
            }
        }

        $html .= '</select>';
        $html .= '<button type="submit">' . $button . '</button>';
        $html .= '</div>';
        $html .= '</form>';

        return $html;
    }

}
