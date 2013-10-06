<?php

include_once(DOKU_INC . "/inc/components/action.php");
include_once(DOKU_INC . "/inc/components/actions/edit_common.php");

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
