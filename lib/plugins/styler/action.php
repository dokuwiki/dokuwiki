<?php
/**
 * DokuWiki Plugin styler (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Andreas Gohr <andi@splitbrain.org>
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

/**
 * Class action_plugin_styler
 *
 * This handles all the save actions and loading the interface
 *
 * All this usually would be done within an admin plugin, but we want to have this available outside
 * the admin interface using our floating dialog.
 */
class action_plugin_styler extends DokuWiki_Action_Plugin {

    /**
     * Registers a callback functions
     *
     * @param Doku_Event_Handler $controller DokuWiki's event controller object
     * @return void
     */
    public function register(Doku_Event_Handler $controller) {
        $controller->register_hook('AJAX_CALL_UNKNOWN', 'BEFORE', $this, 'handle_ajax');
        $controller->register_hook('ACTION_ACT_PREPROCESS', 'BEFORE', $this, 'handle_action');
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
        if($ACT != 'admin' || $INPUT->str('page') != 'styler') return;
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

    /**
     * Updates the style.ini settings by passing it on to handle() of the admin component
     *
     * @param Doku_Event $event  event object by reference
     * @param mixed      $param  [the parameters passed as fifth argument to register_hook() when this
     *                           handler was registered]
     * @return void
     */
    public function handle_action(Doku_Event &$event, $param) {
        if($event->data != 'styler_plugin') return;
        if(!auth_isadmin()) return;
        $event->data = 'show';

        /** @var admin_plugin_styler $hlp */
        $hlp = plugin_load('admin', 'styler');
        $hlp->handle();
    }

    /**
     * Create the style form in the floating Dialog
     *
     * @param Doku_Event $event  event object by reference
     * @param mixed      $param  [the parameters passed as fifth argument to register_hook() when this
     *                           handler was registered]
     * @return void
     */

    public function handle_ajax(Doku_Event &$event, $param) {
        if($event->data != 'plugin_styler') return;
        if(!auth_isadmin()) return;
        $event->preventDefault();
        $event->stopPropagation();

        global $ID;
        global $INPUT;
        $ID = getID();

        /** @var admin_plugin_styler $hlp */
        $hlp = plugin_load('admin', 'styler');
        if($INPUT->str('run') == 'preview') {
            $hlp->run_preview();
        } else {
            $hlp->form(true);
        }
    }

}

// vim:ts=4:sw=4:et:
