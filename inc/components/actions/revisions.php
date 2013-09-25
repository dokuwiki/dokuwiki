<?php

/**
 * Handler for action revisions
 * 
 * @author Junling Ma <junlingm@gmail.com>
 */
class Doku_Action_Revisions extends Doku_Action
{
    /**
     * The Doku_Action interface to specify the action name that this
     * handler can handle.
     * 
     * @return string the action name
     */
    public function action() {
        return "revisions";
    }

    /**
     * The Doku_Action interface to specify the required permission level
     * to handle action revisions
     * 
     * @return string the permission
     */
    public function permission_required() {
        return AUTH_READ;
    }

    /**
     * The Doku_Action interface to return the html for display for 
     * action revisions.
     * 
     * @global array $INPUT
     */
    function html(){
        global $INPUT;
        html_revisions($INPUT->int('first'));
    }
}

