<?php
/**
 * DokuWiki Plugin styling (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Andreas Gohr <andi@splitbrain.org>
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

/**
 * Class action_plugin_styling
 *
 * This handles all the save actions and loading the interface
 *
 * All this usually would be done within an admin plugin, but we want to have this available outside
 * the admin interface using our floating dialog.
 */
class action_plugin_styling extends DokuWiki_Action_Plugin {

    /**
     * Registers a callback functions
     *
     * @param Doku_Event_Handler $controller DokuWiki's event controller object
     * @return void
     */
    public function register(Doku_Event_Handler $controller) {
        $controller->register_hook('TPL_METAHEADER_OUTPUT', 'BEFORE', $this, 'handle_header');
    }

    /**
     * Adds the preview parameter to the stylesheet loading in non-js mode
     *
     * @param Doku_Event $event  event object by reference
     * @param mixed      $param  [the parameters passed as fifth argument to register_hook() when this
     *                           handler was registered]
     * @return void
     */
    public function handle_header(Doku_Event &$event, $param) {
        global $ACT;
        global $INPUT;
        if($ACT != 'admin' || $INPUT->str('page') != 'styling') return;
        if(!auth_isadmin()) return;

        // set preview
        $len = count($event->data['link']);
        for($i = 0; $i < $len; $i++) {
            if(
                $event->data['link'][$i]['rel'] == 'stylesheet' &&
                strpos($event->data['link'][$i]['href'], 'lib/exe/css.php') !== false
            ) {
                $event->data['link'][$i]['href'] .= '&preview=1&tseed='.time();
            }
        }
    }

}

// vim:ts=4:sw=4:et:
