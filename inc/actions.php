<?php

/**
 * DokuWiki Actions
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

use dokuwiki\ActionRouter;
use dokuwiki\Extension\Event;

/**
 * All action processing starts here
 */
function act_dispatch()
{
    // always initialize on first dispatch (test request may dispatch mutliple times on one request)
    $router = ActionRouter::getInstance(true);

    $headers = ['Content-Type: text/html; charset=utf-8'];
    Event::createAndTrigger('ACTION_HEADERS_SEND', $headers, 'act_sendheaders');

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
function act_sendheaders($headers)
{
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
function act_clean($act)
{
    // check if the action was given as array key
    if (is_array($act)) {
        [$act] = array_keys($act);
    }

    // no action given
    if ($act === null) return 'show';

    //remove all bad chars
    $act = strtolower($act);
    $act = preg_replace('/[^1-9a-z_]+/', '', $act);

    if ($act == 'export_html') $act = 'export_xhtml';
    if ($act == 'export_htmlbody') $act = 'export_xhtmlbody';

    if ($act === '') $act = 'show';
    return $act;
}
