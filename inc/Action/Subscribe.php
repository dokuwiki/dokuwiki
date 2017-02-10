<?php
/**
 * Created by IntelliJ IDEA.
 * User: andi
 * Date: 2/10/17
 * Time: 12:31 PM
 */

namespace dokuwiki\Action;

class Subscribe extends AbstractUserAction {

    /** @inheritdoc */
    function minimumPermission() {
        return AUTH_READ;
    }

    /** @inheritdoc */
    public function preProcess() {
        $act = $this->actionname;
        try {
            $act = $this->handleSubscribeData();
        } catch(\Exception $e) {
            msg($e->getMessage(), -1);
        }
        return $act;
    }

    /** @inheritdoc */
    public function tplContent() {
        tpl_subscribe();
    }

    /**
     * Handle page 'subscribe'
     *
     * Throws exception on error.
     *
     * @author Adrian Lang <lang@cosmocode.de>
     *
     * @return string action command
     * @throws \Exception if (un)subscribing fails
     */
    protected function handleSubscribeData() {
        global $lang;
        global $INFO;
        global $ID;
        global $INPUT;

        // get and preprocess data.
        $params = array();
        foreach(array('target', 'style', 'action') as $param) {
            if($INPUT->has("sub_$param")) {
                $params[$param] = $INPUT->str("sub_$param");
            }
        }

        // any action given? if not just return and show the subscription page
        if(empty($params['action']) || !checkSecurityToken()) return $this->actionname;

        // Handle POST data, may throw exception.
        trigger_event('ACTION_HANDLE_SUBSCRIBE', $params, 'subscription_handle_post');

        $target = $params['target'];
        $style = $params['style'];
        $action = $params['action'];

        // Perform action.
        $sub = new \Subscription();
        if($action == 'unsubscribe') {
            $ok = $sub->remove($target, $INPUT->server->str('REMOTE_USER'), $style);
        } else {
            $ok = $sub->add($target, $INPUT->server->str('REMOTE_USER'), $style);
        }

        if($ok) {
            msg(
                sprintf(
                    $lang["subscr_{$action}_success"], hsc($INFO['userinfo']['name']),
                    prettyprint_id($target)
                ), 1
            );
            act_redirect($ID, $this->actionname);
        } else {
            throw new \Exception(
                sprintf(
                    $lang["subscr_{$action}_error"],
                    hsc($INFO['userinfo']['name']),
                    prettyprint_id($target)
                )
            );
        }

        // Assure that we have valid data if act_redirect somehow fails. should never be reached
        $INFO['subscribed'] = $sub->user_subscription();
        return 'show';
    }

}
