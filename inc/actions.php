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
    global $INPUT;

    $opts = array(
            'id'       => $id,
            'preact'   => $preact
            );
    //get section name when coming from section edit
    if ($INPUT->has('hid')) {
        // Use explicitly transmitted header id
        $opts['fragment'] = $INPUT->str('hid');
    } else if($PRE && preg_match('/^\s*==+([^=\n]+)/',$TEXT,$match)){
        // Fallback to old mechanism
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


//Setup VIM: ex: et ts=2 :
