<?php

class Doku_Action_Draft extends Doku_Action
{
    public function action() { return "draft"; }

    public function permission_required() { return AUTH_EDIT; }

    public function handle() {
        global $INFO;
        if (!file_exists($INFO['draft'])) return 'edit';
    }
}
