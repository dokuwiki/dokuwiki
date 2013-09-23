<?php

class Doku_Action_Denied extends Doku_Action
{
	public function action() { return "denied"; }

	public function permission_required() { return AUTH_NONE; }
}
