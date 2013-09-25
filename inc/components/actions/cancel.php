<?php

/**
 * Handler for the cancel action
 * 
 * @author Junling Ma <junlingm@gmail.com>
 */
class Doku_Action_Cancel extends Doku_Action
{
    /**
     * The Doku_Action interface to specify the action name that this
     * handler can handle.
     * 
     * @return string the action name
     */
    public function action() {
        return "cancel";
    }

    /**
     * The Doku_Action interface to specify the required permissions
     * for action cancel.
     * 
     * @return string the permission required
     */
    public function permission_required() {
        return AUTH_EDIT;
    }
}
