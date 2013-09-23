<?php

class Doku_Action_Diff extends Doku_Action
{
    public function action() { return "diff"; }

    public function permission_required() { return AUTH_READ; }

    public function handle() {
        global $INPUT;
        $difftype = $INPUT->str('difftype');
        if (!empty($difftype)) {
            set_doku_pref('difftype', $difftype);
        }
    }
}
