<?php

class Doku_Action_Resendpwd extends Doku_Action
{
    public function action() { return "resendpwd"; }

    public function permission_required() { return AUTH_NONE; }

    public function handle() {
        global $conf;
        //disable all acl related commands if ACL is disabled
        if (!$conf['useacl']) {
            msg('Command unavailable: '.htmlspecialchars($act),-1);
            return 'show';
        }
        if (act_resendpwd()) return "login";
    }
}
