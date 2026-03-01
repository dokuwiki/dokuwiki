<?php

use dokuwiki\Extension\ActionPlugin;
use dokuwiki\Extension\EventHandler;
use dokuwiki\Extension\Event;

/**
 * DokuWiki Plugin styling (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Andreas Gohr <andi@splitbrain.org>
 */
class action_plugin_styling extends ActionPlugin
{
    /**
     * Registers a callback functions
     *
     * @param EventHandler $controller DokuWiki's event controller object
     * @return void
     */
    public function register(EventHandler $controller)
    {
        $controller->register_hook('TPL_METAHEADER_OUTPUT', 'BEFORE', $this, 'handleHeader');
    }

    /**
     * Adds the preview parameter to the stylesheet loading in non-js mode
     *
     * @param Event $event event object by reference
     * @param mixed $param [the parameters passed as fifth argument to register_hook() when this
     *                      handler was registered]
     * @return void
     */
    public function handleHeader(Event &$event, $param)
    {
        global $ACT;
        global $INPUT;
        if ($ACT != 'admin' || $INPUT->str('page') != 'styling') return;
        /** @var admin_plugin_styling $admin */
        $admin = plugin_load('admin', 'styling');
        if (!$admin->isAccessibleByCurrentUser()) return;

        // set preview
        $len = count($event->data['link']);
        for ($i = 0; $i < $len; $i++) {
            if (
                $event->data['link'][$i]['rel'] == 'stylesheet' &&
                strpos($event->data['link'][$i]['href'], 'lib/exe/css.php') !== false
            ) {
                $event->data['link'][$i]['href'] .= '&preview=1&tseed=' . time();
            }
        }
    }
}

// vim:ts=4:sw=4:et:
