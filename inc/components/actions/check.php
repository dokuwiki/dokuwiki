<?php

class Doku_Action_Check extends Doku_Action
{
	public function action() { return "check"; }

	public function permission_required() { return AUTH_READ; }

	public function handle() {
		check();
		return "show";
	}
}
