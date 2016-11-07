<?php
/**
 * DokuWiki Plugin struct (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Andreas Gohr, Michael Große <dokuwiki@cosmocode.de>
 */

// must be run within Dokuwiki
use dokuwiki\plugin\struct\meta\StructException;
use dokuwiki\plugin\struct\meta\Title;

if(!defined('DOKU_INC')) die();

/**
 * Class action_plugin_struct_title
 *
 * Saves the page title when meta data is saved
 */
class action_plugin_struct_title extends DokuWiki_Action_Plugin {

    /**
     * Registers a callback function for a given event
     *
     * @param Doku_Event_Handler $controller DokuWiki's event controller object
     * @return void
     */
    public function register(Doku_Event_Handler $controller) {
        $controller->register_hook('PARSER_METADATA_RENDER', 'AFTER', $this, 'handle_meta');
    }

    /**
     * Store the page's title
     *
     * @param Doku_Event $event
     * @param $param
     */
    public function handle_meta(Doku_Event $event, $param) {
        $id = $event->data['current']['last_change']['id'];
        try {
            $title = new Title($id);

            if(!blank($event->data['current']['title'])) {
                $title->setTitle($event->data['current']['title']);
            } else {
                $title->setTitle(null);
            }
        } catch(StructException $e) {
            msg($e->getMessage(), -1);
        }
    }

}
