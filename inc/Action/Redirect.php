<?php

namespace dokuwiki\Action;

use dokuwiki\Action\Exception\ActionAbort;
use dokuwiki\Extension\Event;

/**
 * Class Redirect
 *
 * Used to redirect to the current page with the last edited section as a target if found
 *
 * @package dokuwiki\Action
 */
class Redirect extends AbstractAliasAction {

    /**
     * Redirect to the show action, trying to jump to the previously edited section
     *
     * @triggers ACTION_SHOW_REDIRECT
     * @throws ActionAbort
     */
    public function preProcess() {
        global $PRE;
        global $TEXT;
        global $INPUT;
        global $ID;
        global $ACT;

        $opts = array(
            'id' => $ID,
            'preact' => $ACT
        );
        //get section name when coming from section edit
        if($INPUT->has('hid')) {
            // Use explicitly transmitted header id
            $opts['fragment'] = $INPUT->str('hid');
        } else if($PRE && preg_match('/^\s*==+([^=\n]+)/', $TEXT, $match)) {
            // Fallback to old mechanism
            $check = false; //Byref
            $opts['fragment'] = sectionID($match[0], $check);
        }

        // execute the redirect
        Event::createAndTrigger('ACTION_SHOW_REDIRECT', $opts, array($this, 'redirect'));

        // should never be reached
        throw new ActionAbort('show');
    }

    /**
     * Execute the redirect
     *
     * Default action for ACTION_SHOW_REDIRECT
     *
     * @param array $opts id and fragment for the redirect and the preact
     */
    public function redirect($opts) {
        $go = wl($opts['id'], '', true, '&');
        if(isset($opts['fragment'])) $go .= '#' . $opts['fragment'];

        //show it
        send_redirect($go);
    }
}
