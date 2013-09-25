<?php

/**
 * The action handler for locked
 * 
 * @author Junling Ma <junlingmgmail.com>
 */
class Doku_Action_Locked extends Doku_Action {
    /**
     * The Doku_Action interface to specify the action name that this handler
     * can handle.
     * 
     * @return string action name
     */
    public function action() {
        return "locked";
    }

    /**
     * The Doku_Action interface to specify the required permission for
     * action locked
     * 
     * @return string permission required for action loacked
     */
    public function permission_required() {
        return AUTH_NONE;
    }

    /**
     * The Doku_Action interface to return the html for action locked
     * Display errors on locked page.
     * was html_locked() by Andreas Gohr <andi@splitbrain.org>
     * 
     * @global string $ID
     * @global array $conf
     * @global array $lang
     * @global array $INFO
     */
    public function html() {
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
