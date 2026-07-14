<?php

use dokuwiki\Extension\ActionPlugin;
use dokuwiki\Extension\Event;
use dokuwiki\Extension\EventHandler;
use dokuwiki\plugin\extension\Extension;
use dokuwiki\plugin\extension\GuiExtension;

/** DokuWiki Plugin extension (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Andreas Gohr <andi@splitbrain.org>
 */
class action_plugin_extension extends ActionPlugin
{
    /**
     * Registers a callback function for a given event
     *
     * @param EventHandler $controller DokuWiki's event controller object
     * @return void
     */
    public function register(EventHandler $controller)
    {
        $controller->register_hook('AJAX_CALL_UNKNOWN', 'BEFORE', $this, 'handleAjaxToggle');
    }

    /**
     * Toggle an extension via AJAX
     *
     * Returns the new HTML for the extension
     *
     * @param Event $event
     * @param $param
     */
    public function handleAjaxToggle(Event $event, $param)
    {
        global $INPUT;

        if ($event->data != 'plugin_extension') return;
        $event->preventDefault();
        $event->stopPropagation();

        /** @var admin_plugin_extension $admin */
        $admin = plugin_load('admin', 'extension');
        if (!$admin->isAccessibleByCurrentUser()) {
            http_status(403);
            echo 'Forbidden';
            exit;
        }

        $ext = $INPUT->str('ext');
        if (!$ext) {
            http_status(400);
            echo 'no extension given';
            return;
        }

        if (getSecurityToken() != $INPUT->str('sectok')) {
            http_status(403);
            echo 'Security Token did not match. Possible CSRF attack.';
            return;
        }

        try {
            $extension = Extension::createFromId($ext);
            $extension->toggle();
        } catch (Exception $e) {
            http_status(500);
            echo $e->getMessage();
            return;
        }

        header('Content-Type: text/html; charset=utf-8');
        echo (new GuiExtension($extension))->render();
    }
}
