<?php

include_once(DOKU_INC . "/inc/components/actions/subscription_common.php");

/**
 * Hndler for action unsubscribe
 * 
 * @author Junling Ma <junlingm@gmail.com>
 */
class Doku_Action_Unsubscribe extends Doku_Action_Subscription_Common
{
    /**
     * Specifies the action name
     * 
     * @return string (the action name)
     */
    public function action() {
        return "unsubscribe";
    }
}
