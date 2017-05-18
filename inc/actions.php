<?php
/**
 * DokuWiki Actions
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

if(!defined('DOKU_INC')) die('meh.');


function act_dispatch(){
    $router = \dokuwiki\ActionRouter::getInstance(); // is this needed here or could we delegate it to tpl_content() later?

    $headers = array('Content-Type: text/html; charset=utf-8');
    trigger_event('ACTION_HEADERS_SEND',$headers,'act_sendheaders');

    // clear internal variables
    unset($router);
    unset($headers);
    // make all globals available to the template
    extract($GLOBALS);

    include(template('main.php'));
    // output for the commands is now handled in inc/templates.php
    // in function tpl_content()
}

/**
 * Send the given headers using header()
 *
 * @param array $headers The headers that shall be sent
 */
function act_sendheaders($headers) {
    foreach ($headers as $hdr) header($hdr);
}

/**
 * Sanitize the action command
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 *
 * @param array|string $act
 * @return string
 */
function act_clean($act){
    // check if the action was given as array key
    if(is_array($act)){
        list($act) = array_keys($act);
    }

    //remove all bad chars
    $act = strtolower($act);
    $act = preg_replace('/[^1-9a-z_]+/','',$act);

    if($act == 'export_html') $act = 'export_xhtml';
    if($act == 'export_htmlbody') $act = 'export_xhtmlbody';

    if($act === '') $act = 'show';
    return $act;
}


/**
 * Handle 'draftdel'
 *
 * Deletes the draft for the current page and user
 *
 * @param string $act action command
 * @return string action command
 */
function act_draftdel($act){
    global $INFO;
    @unlink($INFO['draft']);
    $INFO['draft'] = null;
    return 'show';
}

/**
 * Do a redirect after receiving post data
 *
 * Tries to add the section id as hash mark after section editing
 *
 * @param string $id page id
 * @param string $preact action command before redirect
 */
function act_redirect($id,$preact){
    global $PRE;
    global $TEXT;

    $opts = array(
            'id'       => $id,
            'preact'   => $preact
            );
    //get section name when coming from section edit
    if($PRE && preg_match('/^\s*==+([^=\n]+)/',$TEXT,$match)){
        $check = false; //Byref
        $opts['fragment'] = sectionID($match[0], $check);
    }

    trigger_event('ACTION_SHOW_REDIRECT',$opts,'act_redirect_execute');
}

/**
 * Execute the redirect
 *
 * @param array $opts id and fragment for the redirect and the preact
 */
function act_redirect_execute($opts){
    $go = wl($opts['id'],'',true);
    if(isset($opts['fragment'])) $go .= '#'.$opts['fragment'];

    //show it
    send_redirect($go);
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
 * @throws Exception
 */
function subscription_handle_post(&$params) {
    global $INFO;
    global $lang;
    /* @var Input $INPUT */
    global $INPUT;

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
                                        $INPUT->server->str('REMOTE_USER'),
                                        prettyprint_id($target)));
        }
        // subscription_set deletes a subscription if style = null.
        $style = null;
    }

    $params = compact('target', 'style', 'action');
}

//Setup VIM: ex: et ts=2 :
