<?php

include_once(DOKU_INC . "/inc/components/action.php");

/**
 * Handler for action revisions
 * 
 * @author Junling Ma <junlingm@gmail.com>
 */
class Doku_Action_Revisions extends Doku_Action
{
    /**
     * Specifies the action name
     * 
     * @return string the action name
     */
    public function action() {
        return "revisions";
    }

    /**
     * Specifies the required permission level to show old revisions
     * 
     * @return string the permission
     */
    public function permission_required() {
        return AUTH_READ;
    }

    /**
     * Display old revisions.
     * 
     * @global array $INPUT
     */
    function html(){
        global $INPUT;
        html_revisions($INPUT->int('first'));
    }
}

