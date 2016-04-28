<?php
/**
 * DokuWiki Plugin struct (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Andreas Gohr, Michael Große <dokuwiki@cosmocode.de>
 */

// must be run within Dokuwiki
use dokuwiki\plugin\struct\meta\Assignments;
use dokuwiki\plugin\struct\meta\Schema;

if(!defined('DOKU_INC')) die();

class action_plugin_struct_move extends DokuWiki_Action_Plugin {

    /**
     * Registers a callback function for a given event
     *
     * @param Doku_Event_Handler $controller DokuWiki's event controller object
     * @return void
     */
    public function register(Doku_Event_Handler $controller) {
        $controller->register_hook('PLUGIN_MOVE_PAGE_RENAME', 'AFTER', $this, 'handle_move');
    }

    /**
     * Renames all occurances of a page ID in the database
     *
     * @param Doku_Event $event event object by reference
     * @param mixed $param [the parameters passed as fifth argument to register_hook() when this
     *                           handler was registered]
     * @return bool
     */
    public function handle_move(Doku_Event $event, $param) {
        /** @var helper_plugin_struct_db $hlp */
        $hlp = plugin_load('helper', 'struct_db');
        $db = $hlp->getDB();
        if(!$db) return false;

        $old = $event->data['src_id'];
        $new = $event->data['dst_id'];

        // ALL data tables (we don't trust the assigments are still there)
        foreach(Schema::getAll() as $tbl) {
            $sql = "UPDATE data_$tbl SET pid = ? WHERE pid = ?";
            $db->query($sql, array($new, $old));
            $sql = "UPDATE multi_$tbl SET pid = ? WHERE pid = ?";
            $db->query($sql, array($new, $old));
        }
        // assignments
        $sql = "UPDATE schema_assignments SET pid = ? WHERE pid = ?";
        $db->query($sql, array($new, $old));
        // make sure assignments still match patterns;
        $assignments = new Assignments();
        $assignments->reevaluatePageAssignments($new);

        return true;
    }
}
