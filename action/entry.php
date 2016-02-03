<?php
/**
 * DokuWiki Plugin struct (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Andreas Gohr, Michael Große <dokuwiki@cosmocode.de>
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

use plugin\struct\meta\SchemaData;

class action_plugin_struct_entry extends DokuWiki_Action_Plugin {


    /** @var helper_plugin_sqlite */
    protected $sqlite;

    /**
     * Registers a callback function for a given event
     *
     * @param Doku_Event_Handler $controller DokuWiki's event controller object
     * @return void
     */
    public function register(Doku_Event_Handler $controller) {

        $controller->register_hook('HTML_EDITFORM_OUTPUT', 'BEFORE', $this, 'handle_editform');
        $controller->register_hook('ACTION_ACT_PREPROCESS', 'BEFORE', $this, 'handle_pagesave');

    }

    /**
     * Save values of Schemas but do not interfere with saving the page.
     *
     * @param Doku_Event $event  event object by reference
     * @param mixed      $param  [the parameters passed as fifth argument to register_hook() when this
     *                           handler was registered]
     * @return bool
     */
    public function handle_pagesave(Doku_Event &$event, $param) {
        if (act_clean($event->data) !== "save") return false;

        /** @var \helper_plugin_struct_db $helper */
        $helper = plugin_load('helper', 'struct_db');
        $this->sqlite = $helper->getDB();

        global $ID, $INPUT;

        $res = $this->sqlite->query("SELECT tbl FROM schema_assignments WHERE assign = ?",array($ID,));
        if (!$this->sqlite->res2count($res)) return false;

        $tables = array_map(function ($value){return $value['tbl'];},$this->sqlite->res2arr($res));
        $this->sqlite->res_close($res);

        $structData = $INPUT->arr('Schema');
        $timestamp = $INPUT->int('date');

        foreach ($tables as $table) {
            $schema = new SchemaData($table, $ID, $timestamp);
            $schemaData = $structData[$table];
            foreach ($schema->getColumns() as $col) {
                if ($col->getType()->isMulti()) {
                    $schemaData[$col->getType()->getLabel()] = explode(',',$schemaData[$col->getType()->getLabel()]);
                    $schemaData[$col->getType()->getLabel()] = array_map(function($value){return trim($value);}, $schemaData[$col->getType()->getLabel()]);
                }
            }
            $schema->saveData($schemaData);
        }
        return false;
    }

    /**
     * [Custom event handler which performs action]
     *
     * @param Doku_Event $event  event object by reference
     * @param mixed      $param  [the parameters passed as fifth argument to register_hook() when this
     *                           handler was registered]
     * @return bool
     */
    public function handle_editform(Doku_Event &$event, $param) {

        /** @var \helper_plugin_struct_db $helper */
        $helper = plugin_load('helper', 'struct_db');
        $this->sqlite = $helper->getDB();

        global $ID;

        $res = $this->sqlite->query("SELECT tbl FROM schema_assignments WHERE assign = ?",array($ID,));
        if (!$this->sqlite->res2count($res)) return false;

        $tables = array_map(function ($value){return $value['tbl'];},$this->sqlite->res2arr($res));
        $this->sqlite->res_close($res);

        foreach ($tables as $table) {
            $this->createForm($table, $event->data);
        }

        return true;
    }

    /**
     * @param string $tablename
     * @param Doku_Form $data
     */
    private function createForm($tablename, &$data) {
        global $ID;
        $schema = new SchemaData($tablename, $ID, 0);
        $schemadata = $schema->getData();

        $data->insertElement(4, "<h3>$tablename</h3>");
        $cols = $schema->getColumns();
        usort($cols, function($a, $b){if ($a->getSort()<$b->getSort())return -1;return 1;});

        foreach ($cols as $index => $col) {
            $type = $col->getType();
            $label = $type->getLabel();
            $name = "Schema[$tablename][$label]";
            $input = $type->valueEditor($name, $schemadata[$label]);
            $element = "<label>$label $input</label><br />";
            $data->insertElement(5 + $index, $element);
        }
    }

}

// vim:ts=4:sw=4:et:
