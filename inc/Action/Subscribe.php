<?php

namespace dokuwiki\Action;

use dokuwiki\Action\Exception\ActionAbort;
use dokuwiki\Action\Exception\ActionDisabledException;
use dokuwiki\Subscriptions\SubscriberManager;
use dokuwiki\Extension\Event;

/**
 * Class Subscribe
 *
 * E-Mail subscription handling
 *
 * @package dokuwiki\Action
 */
class Subscribe extends AbstractUserAction {

    /** @inheritdoc */
    public function minimumPermission() {
        return AUTH_READ;
    }

    /** @inheritdoc */
    public function checkPreconditions() {
        parent::checkPreconditions();

        global $conf;
        if(isset($conf['subscribers']) && !$conf['subscribers']) throw new ActionDisabledException();
    }

    /** @inheritdoc */
    public function preProcess() {
        try {
            $this->handleSubscribeData();
        } catch(ActionAbort $e) {
            throw $e;
        } catch(\Exception $e) {
            msg($e->getMessage(), -1);
        }
    }

    /** @inheritdoc */
    public function tplContent() {
        tpl_subscribe();
    }

    /**
     * Handle page 'subscribe'
     *
     * @author Adrian Lang <lang@cosmocode.de>
     * @throws \Exception if (un)subscribing fails
     * @throws ActionAbort when (un)subscribing worked
     */
    protected function handleSubscribeData() {
        global $lang;
        global $INFO;
        global $INPUT;

        // get and preprocess data.
        $params = array();
        foreach(array('target', 'style', 'action') as $param) {
            if($INPUT->has("sub_$param")) {
                $params[$param] = $INPUT->str("sub_$param");
            }
        }

        // any action given? if not just return and show the subscription page
        if(empty($params['action']) || !checkSecurityToken()) return;

        // Handle POST data, may throw exception.
        Event::createAndTrigger('ACTION_HANDLE_SUBSCRIBE', $params, array($this, 'handlePostData'));

        $target = $params['target'];
        $style = $params['style'];
        $action = $params['action'];

        // Perform action.
        $subManager = new SubscriberManager();
        if($action === 'unsubscribe') {
            $ok = $subManager->remove($target, $INPUT->server->str('REMOTE_USER'), $style);
        } else {
            $ok = $subManager->add($target, $INPUT->server->str('REMOTE_USER'), $style);
        }

        if($ok) {
            msg(
                sprintf(
                    $lang["subscr_{$action}_success"], hsc($INFO['userinfo']['name']),
                    prettyprint_id($target)
                ), 1
            );
            throw new ActionAbort('redirect');
        }

        throw new \Exception(
            sprintf(
                $lang["subscr_{$action}_error"],
                hsc($INFO['userinfo']['name']),
                prettyprint_id($target)
            )
        );
    }

    /**
     * Validate POST data
     *
     * Validates POST data for a subscribe or unsubscribe request. This is the
     * default action for the event ACTION_HANDLE_SUBSCRIBE.
     *
     * @author Adrian Lang <lang@cosmocode.de>
     *
     * @param array &$params the parameters: target, style and action
     * @throws \Exception
     */
    public function handlePostData(&$params) {
        global $INFO;
        global $lang;
        global $INPUT;

        // Get and validate parameters.
        if(!isset($params['target'])) {
            throw new \Exception('no subscription target given');
        }
        $target = $params['target'];
        $valid_styles = array('every', 'digest');
        if(substr($target, -1, 1) === ':') {
            // Allow “list” subscribe style since the target is a namespace.
            $valid_styles[] = 'list';
        }
        $style = valid_input_set(
            'style', $valid_styles, $params,
            'invalid subscription style given'
        );
        $action = valid_input_set(
            'action', array('subscribe', 'unsubscribe'),
            $params, 'invalid subscription action given'
        );

        // Check other conditions.
        if($action === 'subscribe') {
            if($INFO['userinfo']['mail'] === '') {
                throw new \Exception($lang['subscr_subscribe_noaddress']);
            }
        } elseif($action === 'unsubscribe') {
            $is = false;
            foreach($INFO['subscribed'] as $subscr) {
                if($subscr['target'] === $target) {
                    $is = true;
                }
            }
            if($is === false) {
                throw new \Exception(
                    sprintf(
                        $lang['subscr_not_subscribed'],
                        $INPUT->server->str('REMOTE_USER'),
                        prettyprint_id($target)
                    )
                );
            }
            // subscription_set deletes a subscription if style = null.
            $style = null;
        }

        $params = compact('target', 'style', 'action');
    }

}
