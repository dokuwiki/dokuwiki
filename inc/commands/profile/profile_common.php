<?php

/**
 * The common bits for the handlers of profile and profile_delete
 * 
 * @author Junling Ma <junlingm@gmail.com>
 */
abstract class Doku_Action_Profile_Common extends Doku_Action
{
    /**
     * Specify the required permissions for profile management
     * 
     * @return string the permission required
     */
    public function permission_required() {
        return AUTH_NONE;
    }

    /**
     * handler for profile and profile_delete
     * 
     * @global array $conf
     */
    public function handle() {
        global $conf;
        //disable all acl related commands if ACL is disabled
        if (!$conf['useacl']) {
            msg('Command unavailable: '.htmlspecialchars($act),-1);
            return 'show';
        }
        if (!$_SERVER['REMOTE_USER']) return 'login';
    }
}
