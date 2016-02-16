<?php
/**
 * DokuWiki Plugin struct (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Andreas Gohr, Michael Große <dokuwiki@cosmocode.de>
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

use plugin\struct\meta\Assignments;
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
        global $ID, $INPUT;
        if (act_clean($event->data) !== "save") return false;

        /** @var \helper_plugin_struct_db $helper */
        $helper = plugin_load('helper', 'struct_db');
        $this->sqlite = $helper->getDB();

        $res = $this->sqlite->query("SELECT tbl FROM schema_assignments WHERE assign = ?",array($ID,));
        if (!$this->sqlite->res2count($res)) return false;

        $tables = array_map(
            function ($value) {
                return $value['tbl'];
            },
            $this->sqlite->res2arr($res)
        );
        $this->sqlite->res_close($res);

        $structData = $INPUT->arr('Schema');
        $timestamp = time(); //FIXME we should use the time stamp used to save the page data

        foreach ($tables as $table) {
            $schema = new SchemaData($table, $ID, $timestamp);
            if(!$schema->getId()) {
                // this schema is not available for some reason. skip it
                continue;
            }

            $schemaData = $structData[$table];
            foreach ($schema->getColumns() as $col) {
                $type = $col->getType();
                $label = $type->getLabel();
                if ($type->isMulti() && !is_array($schemaData[$label])) {
                    $schemaData[$label] = $type->splitValues($schemaData[$label]);
                }
            }
            $schema->saveData($schemaData);
        }
        return false;
    }

    /*
     * Enhance the editing form with structural data editing
     *
     * @param Doku_Event $event  event object by reference
     * @param mixed      $param  [the parameters passed as fifth argument to register_hook() when this
     *                           handler was registered]
     * @return bool
     */
    public function handle_editform(Doku_Event $event, $param) {
        global $ID;

        $assignments = new Assignments();
        $tables = $assignments->getPageAssignments($ID);

        $html = '';
        foreach($tables as $table) {
            $html .= $this->createForm($table);
        }

        /** @var Doku_Form $form */
        $form = $event->data;
        $html = "<div class=\"struct\">$html</div>";
        $pos = $form->findElementById('wiki__editbar'); // insert the form before the main buttons
        $form->insertElement($pos, $html);

        return true;
    }

    /**
     * Create the form to edit schemadata
     *
     * @param string $tablename
     * @return string The HTML for this schema's form
     */
    protected function createForm($tablename) {
        global $ID;
        global $REV;
        $schema = new SchemaData($tablename, $ID, $REV);
        $schemadata = $schema->getData();

        $html = "<h3>$tablename</h3>";
        foreach($schemadata as $field) {
            $label = $field->getColumn()->getLabel();
            $name = "Schema[$tablename][$label]";
            $input = $field->getValueEditor($name);
            $element = "<label>$label $input</label><br />";
            $html .= $element;
        }

        return $html;
    }

}

// vim:ts=4:sw=4:et:
