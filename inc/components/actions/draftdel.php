<?php

class Doku_Action_Draftdel extends Doku_Action
{
	public function action() { return "draftdel"; }

	public function permission_required() { return AUTH_EDIT; }

	public function handle() {
		global $INFO;
		@unlink($INFO['draft']);
		$INFO['draft'] = null;
		return 'show';
	}
}