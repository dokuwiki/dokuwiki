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
use plugin\struct\meta\ValidationException;
use plugin\struct\meta\Validator;
use plugin\struct\meta\Value;
use plugin\struct\types\AbstractBaseType;

/**
 * Handles saving from bureaucracy forms
 *
 * This registers to the template action of the bureaucracy plugin and saves all struct data
 * submitted through the bureaucracy form to all newly created pages (if the schema applies)
 */
class action_plugin_struct_bureaucracy extends DokuWiki_Action_Plugin {

    /**
     * Registers a callback function for a given event
     *
     * @param Doku_Event_Handler $controller DokuWiki's event controller object
     * @return void
     */
    public function register(Doku_Event_Handler $controller) {
        $controller->register_hook('PLUGIN_BUREAUCRACY_TEMPLATE_SAVE', 'AFTER', $this, 'handle_save');
    }

    /**
     * Save the struct data
     *
     * @param Doku_Event $event event object by reference
     * @param mixed $param [the parameters passed as fifth argument to register_hook() when this
     *                           handler was registered]
     * @return bool
     */
    public function handle_save(Doku_Event $event, $param) {
        // get all struct values and their associated schemas
        $tosave = array();
        foreach($event->data['fields'] as $field) {
            if(!is_a($field, 'helper_plugin_struct_field')) continue;
            /** @var helper_plugin_struct_field $field */
            $tbl = $field->column->getTable();
            $lbl = $field->column->getLabel();
            if(!isset($tosave[$tbl])) $tosave[$tbl] = array();
            $tosave[$tbl][$lbl] = $field->getParam('value');
        }

        // save all the struct data of assigned schemas
        $id = $event->data['id'];

        $validator = new Validator();
        if(!$validator->validate($tosave, $id)) return false;
        $tosave = $validator->getCleanedData();
        foreach($tosave as $table => $data) {
            $time = filemtime(wikiFN($id));
            $schemaData = new SchemaData($table, $id, $time);
            $schemaData->saveData($data);
        }

        return true;
    }

}

// vim:ts=4:sw=4:et:
