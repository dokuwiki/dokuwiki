<?php
/**
 * DokuWiki Plugin struct (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Andreas Gohr, Michael Große <dokuwiki@cosmocode.de>
 */

// must be run within Dokuwiki
use dokuwiki\plugin\struct\meta\Assignments;
use dokuwiki\plugin\struct\meta\SchemaData;

if(!defined('DOKU_INC')) die();

class action_plugin_struct_diff extends DokuWiki_Action_Plugin {

    /**
     * Registers a callback function for a given event
     *
     * @param Doku_Event_Handler $controller DokuWiki's event controller object
     * @return void
     */
    public function register(Doku_Event_Handler $controller) {
        $controller->register_hook('IO_WIKIPAGE_READ', 'AFTER', $this, 'handle_diffload');
    }

    /**
     * Add structured data to the diff
     *
     * This is done by adding pseudo syntax to the page source when it is loaded in diff context
     *
     * @param Doku_Event $event event object by reference
     * @param mixed $param [the parameters passed as fifth argument to register_hook() when this
     *                           handler was registered]
     * @return bool
     */
    public function handle_diffload(Doku_Event $event, $param) {
        global $ACT;
        global $INFO;
        if($ACT != 'diff') return;
        $id = $event->data[2];
        $rev = $event->data[3];
        if($INFO['id'] != $id) return;

        $assignments = new Assignments();
        $tables = $assignments->getPageAssignments($id);
        if(!$tables) return;

        $event->result .= "\n---- struct data ----\n";
        foreach($tables as $table) {
            $schemadata = new SchemaData($table, $id, $rev);
            $event->result .= $schemadata->getDataPseudoSyntax();
        }
        $event->result .= "----\n";
    }

}

// vim:ts=4:sw=4:et:
