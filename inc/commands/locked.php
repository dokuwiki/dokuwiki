<?php

/**
 * The action handler for locked
 * 
 * @author Junling Ma <junlingmgmail.com>
 */
class Doku_Action_Locked extends Doku_Action {
    /**
     * Specifies the action name
     * 
     * @return string action name
     */
    public function action() {
        return "locked";
    }

    /**
     * Specifies the required permission for displaying the locked error.
     * The permission was originally AUTH_NONE. But shouldn't require 
     * at least the read permission?
     * 
     * @return string permission required for action loacked
     */
    public function permission_required() {
        return AUTH_READ;
    }

    /**
     * The handler for the locked action
     * checks if the page is really locked
     */
    public function handle() {
        if (!checklock($ID)) return "show";
    }
}

/**
 * Renderer for the locked action
 * 
 * @author Junling Ma <junlingmgmail.com>
 */
class Doku_Action_Renderer_Locked extends Doku_Action_Renderer
{
    /**
     * Specifies the action name
     * 
     * @return string action name
     */
    public function action() {
        return "locked";
    }

    /**
     * Display errors on locked page.
     * was html_locked() by Andreas Gohr <andi@splitbrain.org>
     * 
     * @global string $ID
     * @global array $conf
     * @global array $lang
     * @global array $INFO
     */
    public function xhtml() {
        global $ID;
        global $conf;
        global $lang;
        global $INFO;

        $locktime = filemtime(wikiLockFN($ID));
        $expire = dformat($locktime + $conf['locktime']);
        $min    = round(($conf['locktime'] - (time() - $locktime) )/60);

        print p_locale_xhtml('locked');
        print '<ul>';
        print '<li><div class="li"><strong>'.$lang['lockedby'].
              ':</strong> '.editorinfo($INFO['locked']).'</div></li>';
        print '<li><div class="li"><strong>'.$lang['lockexpire'].
              ':</strong> '.$expire.' ('.$min.' min)</div></li>';
        print '</ul>';
    }
}
