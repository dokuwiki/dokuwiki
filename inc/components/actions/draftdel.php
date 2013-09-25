<?php

/**
 * Handler for the draftdel action
 * 
 * @author Junling Ma <junlingm@gmail.com>
 */
class Doku_Action_Draftdel extends Doku_Action
{
    /**
     * The Doku_Action interface to specify the action name that this
     * handler can handle.
     * 
     * @return string the action name
     */
    public function action() {
        return "draftdel";
    }

    /**
     * The Doku_Action interface to specify the required permissions
     * for action show.
     * 
     * @return string the permission required
     */
    public function permission_required() {
        return AUTH_EDIT;
    }

    /**
     * Doku_Action interface for handling the draftdel action
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