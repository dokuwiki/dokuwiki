<?php

/**
 * Handle 'save'
 *
 * Checks for spam and conflicts and saves the page.
 * Does a redirect to show the page afterwards or
 * returns a new action.
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function act_save(){
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

class Doku_Action_Save extends Doku_Action
{
    public function action() { return "save"; }

    public function permission_required() { return AUTH_EDIT; }

    public function handle() {
        if (checkSecurityToken()) return act_save();
        return "preview";
    }
}
