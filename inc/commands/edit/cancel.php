<?php

/**
 * Handler for the cancel action
 * 
 * @author Junling Ma <junlingm@gmail.com>
 */
class Doku_Action_Cancel extends Doku_Action
{
    /**
     * Specifies the action name
     * 
     * @return string the action name
     */
    public function action() {
        return "cancel";
    }

    /**
     * Specifies the required permissions to cancel an edit
     * 
     * @return string the permission required
     */
    public function permission_required() {
        return AUTH_EDIT;
    }

    /**
     * Handles canceling an edit.
     * 
     * @return string the next action to perform
     */
    public function handle() {
        return "show";
    }
}
