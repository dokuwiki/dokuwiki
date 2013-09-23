<?php

class Doku_Action_Show extends Doku_Action
{
	public function action() { return "show"; }

	public function permission_required() { return AUTH_READ; }
}
