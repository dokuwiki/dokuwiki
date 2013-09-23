<?php

class Doku_Action_Index extends Doku_Action
{
    public function action() { return "index"; }

    public function permission_required() { return AUTH_EDIT; }
}
