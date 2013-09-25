<?php

/**
 * Handler for the save action
 * 
 * @author Junling Ma <junlingm@gmail.com>
 */
class Doku_Action_Save extends Doku_Action
{
    /**
     * The Doku_Action interface to specify the action name that this
     * handler can handle.
     * 
     * @return string the action name
     */
    public function action() {
        return "save";
    }

    /**
     * The Doku_Action interface to specify the required permissions
     * for action show.
     * 
     * @return string the permission required
     */
    public function permission_required() {
        return AUTH_EDIT;
    }

    /**
     * Doku_Action interface for handling the save action.
     * Saves a wiki page. Was act_save() by
     * @author Andreas Gohr <andi@splitbrain.org>
     *
     * Checks for spam and conflicts and saves the page.
     * Does a redirect to show the page afterwards or
     * returns a new action.
     * 
     * @global string $ID
     * @global string $DATE
     * @global string $PRE
     * @global string $TEXT
     * @global string $SUF
     * @global string $SUM
     * @global string $lang
     * @global array $INFO
     * @global array $INPUT
     * @return string the next action
     */
    public function handle() {
        if (!checkSecurityToken())
            return "preview";

        global $ID;
        global $DATE;
        global $PRE;
        global $TEXT;
        global $SUF;
        global $SUM;
        global $lang;
        global $INFO;
        global $INPUT;

        //spam check
        if(checkwordblock()) {
            msg($lang['wordblock'], -1);
            return 'edit';
        }
        //conflict check
        if($DATE != 0 && $INFO['meta']['date']['modified'] > $DATE )
            return 'conflict';

        //save it
        saveWikiText($ID,con($PRE,$TEXT,$SUF,1),$SUM,$INPUT->bool('minor')); //use pretty mode for con
        //unlock it
        unlock($ID);

        session_write_close();

        // when done, show page
        return 'draftdel';
    }
}
