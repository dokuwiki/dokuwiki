<?php

/**
 * Handle 'login', 'logout'
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function act_auth($act){
    global $ID;
    global $INFO;
    global $conf;

    //disable all acl related commands if ACL is disabled
    if (!$conf['useacl']) {
        msg('Command unavailable: '.htmlspecialchars($act),-1);
        return 'show';
    }

    //already logged in?
    if(isset($_SERVER['REMOTE_USER']) && $act=='login'){
        return 'show';
    }

    //handle logout
    if($act=='logout'){
        $lockedby = checklock($ID); //page still locked?
        if($lockedby == $_SERVER['REMOTE_USER'])
            unlock($ID); //try to unlock

        // do the logout stuff
        auth_logoff();

        // rebuild info array
        $INFO = pageinfo();

        return "login";
    }

    return $act;
}

class Doku_Action_Login extends Doku_Action
{
    public function action() { return "login"; }

    public function permission_required() { return AUTH_NONE; }

    public function handle() { return act_auth($this->action()); }
}

class Doku_Action_Logout extends Doku_Action
{
    public function action() { return "logout"; }

    public function permission_required() { return AUTH_NONE; }

    public function handle() { return act_auth($this->action()); }
}
