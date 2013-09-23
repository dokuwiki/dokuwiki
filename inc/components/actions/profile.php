<?php

class Doku_Action_Profile extends Doku_Action
{
	public function action() { return "profile"; }

	public function permission_required() { return AUTH_NONE; }
	
	public function handle() {
		global $conf;
		//disable all acl related commands if ACL is disabled
		if (!$conf['useacl']) {
			msg('Command unavailable: '.htmlspecialchars($act),-1);
			return 'show';
		}
		if (!$_SERVER['REMOTE_USER']) return 'login';
		if (updateprofile()) {
        	msg($lang['profchanged'],1);
        	return 'show';
        }
    }
}

class Doku_Action_Profile_Delete extends Doku_Action
{
	public function action() { return "profile_delete"; }

	public function permission_required() { return AUTH_NONE; }
	
	public function handle() {
		global $conf;
		//disable all acl related commands if ACL is disabled
		if (!$conf['useacl']) {
			msg('Command unavailable: '.htmlspecialchars($act),-1);
			return 'show';
		}
		if (!$_SERVER['REMOTE_USER']) return 'login';
		if (auth_deleteprofile()) {
			msg($lang['profdeleted'],1);
            return 'show';
        }
		return 'profile';
	}
}
