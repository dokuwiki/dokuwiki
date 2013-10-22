<?php

include_once(dirname(__FILE__) . '/subscription_common.php');

/**
 * Handler for action subscribe
 * 
 * @author Junling Ma <junlingm@gmail.com>
 */
class Doku_Action_Subscribe extends Doku_Action_Subscription_Common
{
    /**
     * Specifies the action name
     * 
     * @return string the action name
     */
    public function action() {
        return "subscribe";
    }
}

/**
 * Renderer for action subscribe
 * 
 * @author Junling Ma <junlingm@gmail.com>
 */
class Doku_Action_Renderer_Subscribe extends Doku_Action_Renderer_Subscription_Common
{
    /**
     * Specifies the action name
     * 
     * @return string the action name
     */
    public function action() {
        return "subscribe";
    }
}
