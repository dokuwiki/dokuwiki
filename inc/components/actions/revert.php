<?php

include_once(DOKU_INC . "/inc/components/action.php");

/**
 * The revert action handler.
 * 
 * @author Junling Ma <junlingm@gmail.com>
 */
class Doku_Action_Revert extends Doku_Action
{
    /**
     * Specifies the action name
     * 
     * @return string the action name
     */
    public function action() {
        return "revert";
    }

    /**
     * Specifies the required permissions to revert to a certain revision
     * 
     * @return string the permission required
     */
    public function permission_required() {
        return AUTH_EDIT;
    }

    /**
     * Revert to a certain revision. Was act_revert() by
     * @author Andreas Gohr <andi@splitbrain.org>
     * 
     * @global string $ID
     * @global string $REV
     * @global string $lang
     * @return string the next action
     */
    public function handle() {
        if (!checkSecurityToken())
            return "show";

        global $ID;
        global $REV;
        global $lang;
        // FIXME $INFO['writable'] currently refers to the attic version
        // global $INFO;
        // if (!$INFO['writable']) {
        //     return 'show';
        // }

        // when no revision is given, delete current one
        // FIXME this feature is not exposed in the GUI currently
        $text = '';
        $sum  = $lang['deleted'];
        if($REV){
            $text = rawWiki($ID,$REV);
            if(!$text) return 'show'; //something went wrong
            $sum = sprintf($lang['restored'], dformat($REV));
        }

        // spam check

        if (checkwordblock($text)) {
            msg($lang['wordblock'], -1);
            return 'edit';
        }

        saveWikiText($ID,$text,$sum,false);
        msg($sum,1);

        session_write_close();

        // when done, show current page
        $_SERVER['REQUEST_METHOD'] = 'post'; //should force a redirect
        $REV = '';
        return 'draftdel';
    }
}
