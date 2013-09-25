<?php

/**
 * The show action handler.
 * 
 * @author Junling Ma <junlingm@gmail.com>
 */
class Doku_Action_Show extends Doku_Action
{
    /**
     * The Doku_Action interface to specify the action name that this
     * handler can handle.
     * 
     * @return string the action name
     */
    public function action() {
        return "show";
    }

    /**
     * The Doku_Action interface to specify the required permissions
     * for action show.
     * 
     * @return string the permission required
     */
    public function permission_required() {
        return AUTH_READ;
    }

    /**
     * The Doku_Action interface to display html for showing a page
     * adapted from html_show() originally written by 
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
