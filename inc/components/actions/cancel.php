<?php

class Doku_Action_Cancel extends Doku_Action
{
	public function action() { return "cancel"; }

	public function permission_required() { return AUTH_EDIT; }
}
