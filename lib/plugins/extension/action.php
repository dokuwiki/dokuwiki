<?php
/** DokuWiki Plugin extension (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Andreas Gohr <andi@splitbrain.org>
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

class action_plugin_extension extends DokuWiki_Action_Plugin {

    /**
     * Registers a callback function for a given event
     *
     * @param Doku_Event_Handler $controller DokuWiki's event controller object
     * @return void
     */
    public function register(Doku_Event_Handler $controller) {

        $controller->register_hook('AJAX_CALL_UNKNOWN', 'BEFORE', $this, 'info');

    }

    /**
     * Create the detail info for a single plugin
     *
     * @param Doku_Event $event
     * @param            $param
     */
    public function info(Doku_Event &$event, $param){
        global $USERINFO;
        global $INPUT;

        if(empty($_SERVER['REMOTE_USER']) || !auth_isadmin($_SERVER['REMOTE_USER'], $USERINFO['grps'])){
            http_status(403);
            echo 'Forbidden';
            exit;
        }

        if($event->data != 'plugin_extension') return;
        $event->preventDefault();
        $event->stopPropagation();

        header('Content-Type: text/html; charset=utf-8');

        $ext = $INPUT->str('ext');
        if(!$ext) {
            echo 'no extension given';
            return;
        }

        /** @var helper_plugin_extension_extension $extension */
        $extension = plugin_load('helper', 'extension_extension');
        $extension->setExtension($ext);

        /** @var helper_plugin_extension_list $list */
        $list = plugin_load('helper', 'extension_list');


        echo $list->make_info($extension);
    }

}

