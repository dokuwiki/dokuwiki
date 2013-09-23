<?php

class Doku_Action_Revisions extends Doku_Action
{
	public function action() { return "revisions"; }

	public function permission_required() { return AUTH_READ; }
}
