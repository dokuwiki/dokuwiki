<?php

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
}

/**
 * Renderer for action revisions
 * 
 * @author Junling Ma <junlingm@gmail.com>
 */
class Doku_Action_Renderer_Revisions extends Doku_Action_Renderer
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
     * Display old revisions.
     * 
     * @global array $INPUT
     */
    function xhtml(){
        global $INPUT;
        html_revisions($INPUT->int('first'));
    }
}

