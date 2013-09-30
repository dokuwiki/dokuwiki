<?php

include_once(DOKU_COMPONENTS_ROOT . DIRECTORY_SEPARATOR . "action.php");

/**
 * The show action handler.
 * 
 * @author Junling Ma <junlingm@gmail.com>
 */
class Doku_Action_Show extends Doku_Action
{
    /**
     * Specifies the action name
     * 
     * @return string the action name
     */
    public function action() {
        return "show";
    }

    /**
     * Specifies the required permissions to show a page.
     * 
     * @return string the permission required
     */
    public function permission_required() {
        return AUTH_READ;
    }

    /**
     * render a page.
     * 
     * Adapted from html_show() originally written by 
     * Andreas Gohr <andi@splitbrain.org>
     * 
     * @global string $ID
     * @global string $REV
     * @global string $HIGH
     * @global array $INFO
     */
    public function html() {
        global $ID;
        global $REV;
        global $HIGH;
        global $INFO;

        // whether to show the secedit buttons
        $secedit = !$REV;
        if ($REV) print p_locale_xhtml('showrev');
        $html = p_wiki_xhtml($ID,$REV,true);
        $html = html_secedit($html,$secedit);
        if($INFO['prependTOC']) $html = tpl_toc(true).$html;
        $html = html_hilight($html,$HIGH);
        echo $html;
    }
}
