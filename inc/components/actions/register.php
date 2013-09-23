<?php

class Doku_Action_Register extends Doku_Action
{
	public function action() { return "register"; }

	public function permission_required() { return AUTH_NONE; }
	
	public function handle() {
		global $INPUT;
		global $conf;
		//disable all acl related commands if ACL is disabled
		if (!$conf['useacl']) {
			msg('Command unavailable: '.htmlspecialchars($act),-1);
			return 'show';
		}
		if ($INPUT->post->bool('save') && register()) return "login";
    }
}

