<?php

class Doku_Action_Search extends Doku_Action
{
    public function action() { return "search"; }

    public function permission_required() { return AUTH_NONE; }

    public function handle() {
        global $QUERY;
        $s = cleanID($QUERY);
        if (empty($s)) return "show";
    }
}
