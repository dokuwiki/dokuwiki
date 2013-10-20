<?php

include_once(DOKU_COMPONENTS_ROOT . DIRECTORY_SEPARATOR . "action.php");

/**
 * The commons between the subscribe and unsubscribe actions
 * 
 * @author Junling Ma <junlingm@gmail.com>
 */
abstract class Doku_Action_Subscription_Common extends Doku_Action
{
    /**
     * Specifies the required permission for subscriptions
     *
     * @return string the required permission
     */
    public function permission_required() {
        return AUTH_READ;
    }

    /**
     * handle subscriptions
     * adapted from act_subscription() by
     * @author Adrian Lang <lang@cosmocode.de>
     * 
     * @global string $lang
     * @global array $INFO
     * @global array $ID
     * @global array $INPUT
     * @global array $conf
     * @return string the next action
     */
    function handle(){
        global $lang;
        global $INFO;
        global $ID;
        global $INPUT;
        global $conf;

        $act = $this->action();
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
        }
        msg(sprintf($lang["subscr_{$action}_error"],
                    hsc($INFO['userinfo']['name']),
                    prettyprint_id($target)), -1);
    }

    /**
     * Display the subscription form
     * Was tpl_subscribe() by
     * @author Adrian Lang <lang@cosmocode.de>
     * @global $INFO
     * @global $ID
     * @global $lang;
     * @global $conf;
     */
    public function html() {
        global $INFO;
        global $ID;
        global $lang;
        global $conf;

        $stime_days = $conf['subscribe_time'] / 60 / 60 / 24;

        echo p_locale_xhtml('subscr_form');
        echo '<h2>'.$lang['subscr_m_current_header'].'</h2>';
        echo '<div class="level2">';
        if($INFO['subscribed'] === false) {
            echo '<p>'.$lang['subscr_m_not_subscribed'].'</p>';
        } else {
            echo '<ul>';
            foreach($INFO['subscribed'] as $sub) {
                echo '<li><div class="li">';
                if($sub['target'] !== $ID) {
                    echo '<code class="ns">'.hsc(prettyprint_id($sub['target'])).'</code>';
                } else {
                    echo '<code class="page">'.hsc(prettyprint_id($sub['target'])).'</code>';
                }
                $sstl = sprintf($lang['subscr_style_'.$sub['style']], $stime_days);
                if(!$sstl) $sstl = hsc($sub['style']);
                echo ' ('.$sstl.') ';

                echo '<a href="'.wl(
                    $ID,
                    array(
                         'do'        => 'subscribe',
                         'sub_target'=> $sub['target'],
                         'sub_style' => $sub['style'],
                         'sub_action'=> 'unsubscribe',
                         'sectok'    => getSecurityToken()
                    )
                ).
                    '" class="unsubscribe">'.$lang['subscr_m_unsubscribe'].
                    '</a></div></li>';
            }
            echo '</ul>';
        }
        echo '</div>';

        // Add new subscription form
        echo '<h2>'.$lang['subscr_m_new_header'].'</h2>';
        echo '<div class="level2">';
        $ns      = getNS($ID).':';
        $targets = array(
            $ID => '<code class="page">'.prettyprint_id($ID).'</code>',
            $ns => '<code class="ns">'.prettyprint_id($ns).'</code>',
        );
        $styles  = array(
            'every'  => $lang['subscr_style_every'],
            'digest' => sprintf($lang['subscr_style_digest'], $stime_days),
            'list'   => sprintf($lang['subscr_style_list'], $stime_days),
        );

        $form = new Doku_Form(array('id' => 'subscribe__form'));
        $form->startFieldset($lang['subscr_m_subscribe']);
        $form->addRadioSet('sub_target', $targets);
        $form->startFieldset($lang['subscr_m_receive']);
        $form->addRadioSet('sub_style', $styles);
        $form->addHidden('sub_action', 'subscribe');
        $form->addHidden('do', 'subscribe');
        $form->addHidden('id', $ID);
        $form->endFieldset();
        $form->addElement(form_makeButton('submit', 'subscribe', $lang['subscr_m_subscribe']));
        html_form('SUBSCRIBE', $form);
        echo '</div>';
    }
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
