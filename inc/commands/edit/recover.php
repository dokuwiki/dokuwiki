<?php

include_once(dirname(__FILE__) . '/edit_common.php');

/**
 * Handler for the recover action
 * 
 * @author Junling Ma <junlingm@gmail.com>
 */
class Doku_Action_Recover extends Doku_Action_Edit_Common
{
    /**
     * Specify the action name
     * 
     * @return string the action name
     */
    public function action() { return "recover"; }
}

/**
 * Renderer for the recover action
 * 
 * @author Junling Ma <junlingm@gmail.com>
 */
class Doku_Action_Renderer_Recover extends Doku_Action_Renderer_Edit_Common
{
    /**
     * Specify the action name
     * 
     * @return string the action name
     */
    public function action() { return "recover"; }
}
