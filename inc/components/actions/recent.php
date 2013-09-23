<?php

class Doku_Action_Recent extends Doku_Action
{
	public function action() { return "recent"; }

	public function permission_required() { return AUTH_READ; }

	public function handle() {
		global $INPUT;
		$show_changes = $INPUT->str('show_changes');
		if (!empty($show_changes)) {
			set_doku_pref('show_changes', $show_changes);
		}
	}
}

