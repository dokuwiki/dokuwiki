<?php
/**
 * DokuWiki Plugin struct (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Andreas Gohr, Michael Große <dokuwiki@cosmocode.de>
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

use dokuwiki\plugin\struct\meta\AccessTable;
use dokuwiki\plugin\struct\meta\Assignments;
use dokuwiki\plugin\struct\meta\AccessTableData;
use dokuwiki\plugin\struct\meta\Validator;
use dokuwiki\plugin\struct\meta\Value;

/**
 * Class action_plugin_struct_entry
 *
 * Handles the whole struct data entry process
 */
class action_plugin_struct_entry extends DokuWiki_Action_Plugin {

    /**
     * @var string The form name we use to transfer schema data
     */
    protected static $VAR = 'struct_schema_data';

    /** @var helper_plugin_sqlite */
    protected $sqlite;

    /** @var  bool has the data been validated correctly? */
    protected $validated;

    /** @var  array these schemas have changed data and need to be saved */
    protected $tosave;

    /**
     * Registers a callback function for a given event
     *
     * @param Doku_Event_Handler $controller DokuWiki's event controller object
     * @return void
     */
    public function register(Doku_Event_Handler $controller) {
        // add the struct editor to the edit form;
        $controller->register_hook('HTML_EDITFORM_OUTPUT', 'BEFORE', $this, 'handle_editform');
        // validate data on preview and save;
        $controller->register_hook('ACTION_ACT_PREPROCESS', 'BEFORE', $this, 'handle_validation');
        // ensure a page revision is created when struct data changes:
        $controller->register_hook('COMMON_WIKIPAGE_SAVE', 'BEFORE', $this, 'handle_pagesave_before');
        // save struct data after page has been saved:
        $controller->register_hook('COMMON_WIKIPAGE_SAVE', 'AFTER', $this, 'handle_pagesave_after');
    }

    /**
     * Enhance the editing form with structural data editing
     *
     * @param Doku_Event $event event object by reference
     * @param mixed $param [the parameters passed as fifth argument to register_hook() when this
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
        $html = "<div class=\"struct_entry_form\">$html</div>";
        $pos = $form->findElementById('wiki__editbar'); // insert the form before the main buttons
        $form->insertElement($pos, $html);

        return true;
    }

    /**
     * Clean up and validate the input data
     *
     * @param Doku_Event $event event object by reference
     * @param mixed $param [the parameters passed as fifth argument to register_hook() when this
     *                           handler was registered]
     * @return bool
     */
    public function handle_validation(Doku_Event $event, $param) {
        global $ID, $INPUT;
        $act = act_clean($event->data);
        if(!in_array($act, array('save', 'preview'))) return false;

        // execute the validator
        $validator = new Validator();
        $this->validated = $validator->validate($INPUT->arr(self::$VAR), $ID);
        $this->tosave = $validator->getChangedSchemas();
        $INPUT->post->set(self::$VAR, $validator->getCleanedData());

        if(!$this->validated) foreach($validator->getErrors() as $error) {
            msg(hsc($error), -1);
        }

        // did validation go through? otherwise abort saving
        if(!$this->validated && $act == 'save') {
            $event->data = 'edit';
        }

        return false;
    }

    /**
     * Check if the page has to be changed
     *
     * @param Doku_Event $event event object by reference
     * @param mixed $param [the parameters passed as fifth argument to register_hook() when this
     *                           handler was registered]
     * @return bool
     */
    public function handle_pagesave_before(Doku_Event $event, $param) {
        if($event->data['contentChanged']) return; // will be saved for page changes
        global $ACT;
        global $REV;

        if(count($this->tosave) || isset($GLOBALS['struct_plugin_force_page_save'])) {
            if(trim($event->data['newContent']) === '') {
                // this happens when a new page is tried to be created with only struct data
                msg($this->getLang('emptypage'), -1);
            } else {
                $event->data['contentChanged'] = true; // save for data changes

                // add a summary
                if(empty($event->data['summary'])) {
                    $event->data['summary'] = $this->getLang('summary');
                }
            }
        } else if($ACT == 'revert' && $REV) {
            // revert actions are not validated, so we need to force changes extra
            $assignments = new Assignments();
            $tosave = $assignments->getPageAssignments($event->data['id']);
            if(count($tosave)) {
                $event->data['contentChanged'] = true; // save for data changes
            }
        }
    }

    /**
     * Save the data
     *
     * When this is called, INPUT data has been validated already. On a restore action, the data is
     * loaded from the database and not validated again.
     *
     * @param Doku_Event $event event object by reference
     * @param mixed $param [the parameters passed as fifth argument to register_hook() when this
     *                           handler was registered]
     * @return bool
     */
    public function handle_pagesave_after(Doku_Event $event, $param) {
        global $INPUT;
        global $ACT;
        global $REV;

        $assignments = new Assignments();

        if($ACT == 'revert' && $REV) {
            // reversion is a special case, we load the data to restore from DB:
            $structData = array();
            $this->tosave = $assignments->getPageAssignments($event->data['id']);
            foreach($this->tosave as $table) {
                $oldData = AccessTable::byTableName($table, $event->data['id'], $REV);
                $structData[$table] = $oldData->getDataArray();
            }
        } else {
            // data comes from the edit form
            $structData = $INPUT->arr(self::$VAR);
        }

        if($event->data['changeType'] == DOKU_CHANGE_TYPE_DELETE && empty($GLOBALS['PLUGIN_MOVE_WORKING'])) {
            // clear all data on delete unless it's a move operation
            $tables = $assignments->getPageAssignments($event->data['id']);
            foreach($tables as $table) {
                $schemaData = AccessTable::byTableName($table, $event->data['id'], time());
                $schemaData->clearData();
            }
        } else {
            // save the provided data
            if($this->tosave) foreach($this->tosave as $table) {
                $schemaData = AccessTable::byTableName($table, $event->data['id'], $event->data['newRevision']);
                $schemaData->saveData($structData[$table]);

                // make sure this schema is assigned
                $assignments->assignPageSchema($event->data['id'], $table);
            }
        }
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
        global $INPUT;
        if (auth_quickaclcheck($ID) == AUTH_READ) return '';
        if (checklock($ID)) return '';
        $schema = AccessTable::byTableName($tablename, $ID, $REV);
        $schemadata = $schema->getData();

        $structdata = $INPUT->arr(self::$VAR);
        if(isset($structdata[$tablename])) {
            $postdata = $structdata[$tablename];
        } else {
            $postdata = array();
        }

        // we need a short, unique identifier to use in the cookie. this should be good enough
        $schemaid = 'SRCT'.substr(str_replace(array('+', '/'), '', base64_encode(sha1($tablename, true))), 0, 5);
        $html = '<fieldset data-schema="' . $schemaid . '">';
        $html .= '<legend>' . hsc($tablename) . '</legend>';
        foreach($schemadata as $field) {
            $label = $field->getColumn()->getLabel();
            if(isset($postdata[$label])) {
                // posted data trumps stored data
                $field->setValue($postdata[$label]);
            }
            $html .=  $this->makeField($field, self::$VAR . "[$tablename][$label]");
        }
        $html .= '</fieldset>';

        return $html;
    }

    /**
     * Create the input field
     *
     * @param Value $field
     * @param String $name field's name
     * @return string
     */
    public function makeField(Value $field, $name) {
        $trans = hsc($field->getColumn()->getTranslatedLabel());
        $hint  = hsc($field->getColumn()->getTranslatedHint());
        $class = $hint ? 'hashint' : '';
        $colname = $field->getColumn()->getFullQualifiedLabel();

        $input = $field->getValueEditor($name);

        // we keep all the custom form stuff the field might produce, but hide it
        if(!$field->getColumn()->isVisibleInEditor()) {
            $hide = 'style="display:none"';
        } else {
            $hide = '';
        }

        $html = '';
        $html .= "<label $hide data-column=\"$colname\">";
        $html .= "<span class=\"label $class\" title=\"$hint\">$trans</span>";
        $html .= "<span class=\"input\">$input</span>";
        $html .= '</label>';

        return $html;
    }
}

// vim:ts=4:sw=4:et:
