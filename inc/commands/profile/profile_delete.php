<?php

include_once(dirname(__FILE__).'/profile_common.php');

/**
 * Handler for action profile_delete
 * 
 * @author Junling Ma <junlingm@gmail.com>
 */
class Doku_Action_Profile_Delete extends Doku_Action_Profile_Common
{
    /**
     * Specifies the action name
     * 
     * @return string the action name
     */
    public function action() {
        return "profile_delete";
    }

    /**
     * handling profile deletion
     * 
     * @return string the next action
     */
    public function handle() {
        $act = parent::handle();
        if ($act) return $act;
        if (auth_deleteprofile()) {
            msg($lang['profdeleted'],1);
            return 'show';
        }
        return 'profile';
    }
}
