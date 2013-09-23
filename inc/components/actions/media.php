<?php

class Doku_Action_Media extends Doku_Action
{
	public function action() { return "media"; }

	public function permission_required() { return AUTH_READ; }
}
