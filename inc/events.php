<?php

/**
 * trigger_event
 *
 * function wrapper to process (create, trigger and destroy) an event
 *
 * @param  string   $name               name for the event
 * @param  mixed    $data               event data
 * @param  callback $action             (optional, default=NULL) default action, a php callback function
 * @param  bool     $canPreventDefault  (optional, default=true) can hooks prevent the default action
 *
 * @return mixed                        the event results value after all event processing is complete
 *                                      by default this is the return value of the default action however
 *                                      it can be set or modified by event handler hooks
 */
function trigger_event($name, &$data, $action=null, $canPreventDefault=true) {

    $evt = new \dokuwiki\Extension\Event($name, $data);
    return $evt->trigger($action, $canPreventDefault);
}
