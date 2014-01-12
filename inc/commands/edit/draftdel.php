<?php

/**
 * Handler for the draftdel action
 * 
 * @author Junling Ma <junlingm@gmail.com>
 */
class Doku_Action_Draftdel extends Doku_Action
{
    /**
     * Specifies the action name
     * 
     * @return string the action name
     */
    public function action() {
        return "draftdel";
    }

    /**
     * Specifies the required permissions to delete a draft
     * 
     * @return string the permission required
     */
    public function permission_required() {
        return AUTH_EDIT;
    }

    /**
     * handling draft deletion
     *
     * @global array $INFO
     * @return string the next action
     */
    public function handle() {
        global $INFO;
        @unlink($INFO['draft']);
        $INFO['draft'] = null;
        return 'show';
    }
}
