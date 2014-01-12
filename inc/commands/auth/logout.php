<?php

/**
 * Handler for action logout
 * 
 * @author Junling Ma <junlingm@gmail.com>
 */
class Doku_Action_Logout extends Doku_Action
{
    /**
     * Specifies the action name
     * 
     * @return string the action name
     */
    public function action() {
        return "logout";
    }

    /**
     * Specifies the required permissions for logout.
     * 
     * @return string (the permission required)
     */
    public function permission_required() {
        return AUTH_NONE;
    }

    /**
     * Handle 'logout'
     * Adapted from act_auth($act) originally written by
     * @author Andreas Gohr <andi@splitbrain.org>
     * 
     * @global string $ID
     * @global array $INFO
     * @global array $conf
     * @return string the next action
     */
    public function handle() {
        global $ID;
        global $INFO;
        global $conf;

        //disable all acl related commands if ACL is disabled
        if (!$conf['useacl']) {
            msg('Command unavailable: '.htmlspecialchars($act),-1);
            return 'show';
        }

        $lockedby = checklock($ID); //page still locked?
        if($lockedby == $_SERVER['REMOTE_USER'])
            unlock($ID); //try to unlock

        // do the logout stuff
        auth_logoff();

        // rebuild info array
        $INFO = pageinfo();

        return "login";
    }
}
