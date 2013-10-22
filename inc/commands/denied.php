<?php

include_once(DOKU_COMPONENTS_ROOT . DIRECTORY_SEPARATOR . "action.php");

/**
 * Handler for action denied
 * 
 * @author Junling Ma <junlingm@gmail.com>
 */
class Doku_Action_Denied extends Doku_Action
{
    /**
     * Specifies the action name
     * 
     * @return string the action name
     */
    public function action() {
        return "denied";
    }

    /**
     * Specify the required permissions to show the denied page.
     * 
     * @return string the permission required
     */
    public function permission_required() {
        return AUTH_NONE;
    }
}


/**
 * Renderer for action denied
 * 
 * @author Junling Ma <junlingm@gmail.com>
 */
class Doku_Action_Renderer_Denied extends Doku_Action_Renderer
{
    /**
     * Specifies the action name
     * 
     * @return string the action name
     */
    public function action() {
        return "denied";
    }

    /**
     * render the denied error page
     */
    public function xhtml() {
        print p_locale_xhtml('denied');
    }
}
