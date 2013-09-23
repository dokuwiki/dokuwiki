<?php

class Doku_Action_Backlink extends Doku_Action
{
	public function action() { return "backlink"; }

	public function permission_required() { return AUTH_READ; }
}
