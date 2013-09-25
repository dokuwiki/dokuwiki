<?php

/**
 * Handler for action denied
 * 
 * @author Junling Ma <junlingm@gmail.com>
 */
class Doku_Action_Denied extends Doku_Action
{
    /**
     * The Doku_Action interface to specify the action name that this
     * handler can handle.
     * 
     * @return string the action name
     */
    public function action() {
        return "denied";
    }

    /**
     * The Doku_Action interface to specify the required permissions
     * for action denied.
     * 
     * @return string the permission required
     */
    public function permission_required() {
        return AUTH_NONE;
    }

    /**
     * Doku_ction interface to display the denied error
     */
    public function html() {
        print p_locale_xhtml('denied');
    }
}
