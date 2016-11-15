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
        $db = $hlp->getDB(false);
        if(!$db) return false;
        $old = $event->data['src_id'];
        $new = $event->data['dst_id'];

        // ALL data tables (we don't trust the assigments are still there)
        foreach(Schema::getAll('page') as $tbl) {
            /** @noinspection SqlResolve */
            $sql = "UPDATE data_$tbl SET pid = ? WHERE pid = ?";
            $db->query($sql, array($new, $old));

            /** @noinspection SqlResolve */
            $sql = "UPDATE multi_$tbl SET pid = ? WHERE pid = ?";
            $db->query($sql, array($new, $old));
        }
        // assignments
        $sql = "UPDATE schema_assignments SET pid = ? WHERE pid = ?";
        $db->query($sql, array($new, $old));
        // make sure assignments still match patterns;
        $assignments = Assignments::getInstance();
        $assignments->reevaluatePageAssignments($new);

        // titles
        $sql = "UPDATE titles SET pid = ? WHERE pid = ?";
        $db->query($sql, array($new, $old));

        // Page Type references
        $this->movePageLinks($db, $old, $new);

        return true;
    }

    /**
     * Handles current values for all Page type columns
     *
     * @param helper_plugin_sqlite $db
     * @param string $old
     * @param string $new
     */
    protected function movePageLinks(helper_plugin_sqlite $db, $old, $new) {
        $schemas = Schema::getAll();

        foreach($schemas as $table) {
            $schema = new Schema($table);
            foreach($schema->getColumns() as $col) {
                if(!is_a($col->getType(), dokuwiki\plugin\struct\types\Page::class)) continue;

                $colref = $col->getColref();
                if($col->isMulti()) {
                    /** @noinspection SqlResolve */
                    $sql = "UPDATE multi_$table
                               SET value = REPLACE(value, ?, ?)
                             WHERE value LIKE ?
                               AND colref = $colref
                               AND latest = 1";

                } else {
                    /** @noinspection SqlResolve */
                    $sql = "UPDATE data_$table
                               SET col$colref = REPLACE(col$colref, ?, ?)
                             WHERE col$colref LIKE ?
                               AND latest = 1";
                }
                $db->query($sql, $old, $new, $old); // exact match
                $db->query($sql, $old, $new, "$old#%"); // match with hashes
            }
        }
    }
}
