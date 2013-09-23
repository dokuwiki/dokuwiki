<?php

/**
 * Handle page 'subscribe'
 *
 * Throws exception on error.
 *
 * @author Adrian Lang <lang@cosmocode.de>
 */
function act_subscription($act){
    global $lang;
    global $INFO;
    global $ID;
    global $INPUT;
    global $conf;
    //disable all acl related commands if ACL is disabled
    if (!$conf['useacl']) {
        msg('Command unavailable: '.htmlspecialchars($act),-1);
        return 'show';
    }

    // subcriptions work for logged in users only
    if(!$_SERVER['REMOTE_USER']) return 'show';

    // get and preprocess data.
    $params = array();
    foreach(array('target', 'style', 'action') as $param) {
        if ($INPUT->has("sub_$param")) {
            $params[$param] = $INPUT->str("sub_$param");
        }
    }

    // any action given? if not just return and show the subscription page
    if(!$params['action'] || !checkSecurityToken()) return $act;

    // Handle POST data, may throw exception.
    trigger_event('ACTION_HANDLE_SUBSCRIBE', $params, 'subscription_handle_post');

    $target = $params['target'];
    $style  = $params['style'];
    $action = $params['action'];

    // Perform action.
    $sub = new Subscription();
    if($action == 'unsubscribe'){
        $ok = $sub->remove($target, $_SERVER['REMOTE_USER'], $style);
    }else{
        $ok = $sub->add($target, $_SERVER['REMOTE_USER'], $style);
    }

    if($ok) {
        msg(sprintf($lang["subscr_{$action}_success"], hsc($INFO['userinfo']['name']),
                    prettyprint_id($target)), 1);
        return $act;
    } else {
        throw new Exception(sprintf($lang["subscr_{$action}_error"],
                                    hsc($INFO['userinfo']['name']),
                                    prettyprint_id($target)));
    }

    // Assure that we have valid data if act_redirect somehow fails.
    $INFO['subscribed'] = $sub->user_subscription();
    return 'show';
}

/**
 * Validate POST data
 *
 * Validates POST data for a subscribe or unsubscribe request. This is the
 * default action for the event ACTION_HANDLE_SUBSCRIBE.
 *
 * @author Adrian Lang <lang@cosmocode.de>
 */
function subscription_handle_post(&$params) {
    global $INFO;
    global $lang;

    // Get and validate parameters.
    if (!isset($params['target'])) {
        throw new Exception('no subscription target given');
    }
    $target = $params['target'];
    $valid_styles = array('every', 'digest');
    if (substr($target, -1, 1) === ':') {
        // Allow “list” subscribe style since the target is a namespace.
        $valid_styles[] = 'list';
    }
    $style  = valid_input_set('style', $valid_styles, $params,
                              'invalid subscription style given');
    $action = valid_input_set('action', array('subscribe', 'unsubscribe'),
                              $params, 'invalid subscription action given');

    // Check other conditions.
    if ($action === 'subscribe') {
        if ($INFO['userinfo']['mail'] === '') {
            throw new Exception($lang['subscr_subscribe_noaddress']);
        }
    } elseif ($action === 'unsubscribe') {
        $is = false;
        foreach($INFO['subscribed'] as $subscr) {
            if ($subscr['target'] === $target) {
                $is = true;
            }
        }
        if ($is === false) {
            throw new Exception(sprintf($lang['subscr_not_subscribed'],
                                        $_SERVER['REMOTE_USER'],
                                        prettyprint_id($target)));
        }
        // subscription_set deletes a subscription if style = null.
        $style = null;
    }

    $params = compact('target', 'style', 'action');
}

class Doku_Action_Subscribe extends Doku_Action
{
    public function action() { return "subscribe"; }

    public function permission_required() { return AUTH_READ; }

    public function handle() {
        //check if user is asking to (un)subscribe a page
        try {
            return act_subscription($this->action());
        } catch (Exception $e) {
            msg($e->getMessage(), -1);
        }
    }
}

class Doku_Action_Unsubscribe extends Doku_Action
{
    public function action() { return "unsubscribe"; }

    public function permission_required() { return AUTH_READ; }

    public function handle() {
        //check if user is asking to (un)subscribe a page
        try {
            return act_subscription($this->action());
        } catch (Exception $e) {
            msg($e->getMessage(), -1);
        }
    }
}
