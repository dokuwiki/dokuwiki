<?php
use dokuwiki\plugin\struct\meta\AccessTable;
use dokuwiki\plugin\struct\meta\AccessTableLookup;
use dokuwiki\plugin\struct\meta\StructException;

/**
 * Allows adding a lookup schema as a bureaucracy action
 *
 */
class helper_plugin_struct_lookup extends helper_plugin_bureaucracy_action {

    /**
     * Performs struct_lookup action
     *
     * @param helper_plugin_bureaucracy_field[] $fields  array with form fields
     * @param string $thanks  thanks message
     * @param array  $argv    array with entries: template, pagename, separator
     * @return array|mixed
     *
     * @throws Exception
     */
    public function run($fields, $thanks, $argv) {
        global $ID;

        // get all struct values and their associated schemas
        $tosave = array();
        foreach($fields as $field) {
            if(!is_a($field, 'helper_plugin_struct_field')) continue;
            /** @var helper_plugin_struct_field $field */
            $tbl = $field->column->getTable();
            $lbl = $field->column->getLabel();
            if(!isset($tosave[$tbl])) $tosave[$tbl] = array();
            $tosave[$tbl][$lbl] = $field->getParam('value');
        }

        foreach($tosave as $table => $data) {
            $access = AccessTable::byTableName($table, 0, 0);
            if (!$access instanceof AccessTableLookup) continue;

            if(!$access->getSchema()->isEditable()) {
                msg('lookup save error: no permission for schema', -1);
                return false;
            }
            $validator = $access->getValidator($data);
            if($validator->validate()) {
                $validator->saveData();
            }
        }

        // set thank you message
        if(!$thanks) {
            $thanks = sprintf($this->getLang('bureaucracy_action_struct_lookup_thanks'), wl($ID));
        } else {
            $thanks = hsc($thanks);
        }

        return $thanks;
    }
}