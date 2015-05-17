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
     * Registers a callback function for a given event
     *
     * @param Doku_Event_Handler $controller DokuWiki's event controller object
     * @return void
     */
    public function register(Doku_Event_Handler $controller) {
        $controller->register_hook('AJAX_CALL_UNKNOWN', 'BEFORE', $this, 'handle_ajax');
        $controller->register_hook('ACTION_ACT_PREPROCESS', 'BEFORE', $this, 'handle_action');

        // FIXME load preview style when on admin page
    }

    /**
     * [Custom event handler which performs action]
     *
     * @param Doku_Event $event  event object by reference
     * @param mixed      $param  [the parameters passed as fifth argument to register_hook() when this
     *                           handler was registered]
     * @return void
     */
    public function handle_action(Doku_Event &$event, $param) {
        if(!auth_isadmin()) return;
        if($event->data != 'styler_plugin') return;
        $event->data = 'show';

        /** @var admin_plugin_styler $hlp */
        $hlp = plugin_load('admin', 'styler');
        $hlp->handle();
    }

    /**
     * [Custom event handler which performs action]
     *
     * @param Doku_Event $event  event object by reference
     * @param mixed      $param  [the parameters passed as fifth argument to register_hook() when this
     *                           handler was registered]
     * @return void
     */

    public function handle_ajax(Doku_Event &$event, $param) {
        if(!auth_isadmin()) return;
        if($event->data != 'plugin_styler') return;
        $event->preventDefault();
        $event->stopPropagation();

        /** @var admin_plugin_styler $hlp */
        $hlp = plugin_load('admin', 'styler');
        $hlp->form(true);
    }

}

// vim:ts=4:sw=4:et:
