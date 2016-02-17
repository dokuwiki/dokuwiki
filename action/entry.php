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
use plugin\struct\types\AbstractBaseType;

class action_plugin_struct_entry extends DokuWiki_Action_Plugin {

    /**
     * @var string The form name we use to transfer schema data
     */
    protected static $VAR = 'struct_schema_data';

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
     * Validate the input data and save on ACT=save.
     *
     * @param Doku_Event $event event object by reference
     * @param mixed $param [the parameters passed as fifth argument to register_hook() when this
     *                           handler was registered]
     * @return bool
     */
    public function handle_pagesave(Doku_Event &$event, $param) {
        global $ID, $INPUT;
        $act = act_clean($event->data);
        if(!in_array($act, array('save', 'preview'))) return false;

        $assignments = new Assignments();
        $tables = $assignments->getPageAssignments($ID);
        $structData = $INPUT->arr(self::$VAR);
        $timestamp = time(); //FIXME we should use the time stamp used to save the page data

        $ok = true;
        foreach($tables as $table) {
            $schema = new SchemaData($table, $ID, $timestamp);
            if(!$schema->getId()) {
                // this schema is not available for some reason. skip it
                continue;
            }

            $schemaData = $structData[$table];
            foreach($schema->getColumns() as $col) {
                // fix multi value types
                $type = $col->getType();
                $label = $type->getLabel();
                $trans = $type->getTranslatedLabel();
                if($type->isMulti() && !is_array($schemaData[$label])) {
                    $schemaData[$label] = $type->splitValues($schemaData[$label]);
                }

                // validate data
                $ok = $ok & $this->validate($type, $trans, $schemaData[$label]);
            }

            // save if validated okay
            if($ok && $act == 'save') {
                $schema->saveData($schemaData);
            }

            // write back cleaned up schemaData
            $structData[$table] = $schemaData;
        }
        // write back cleaned up structData
        $INPUT->post->set('Schema', $structData);

        // did validation go through? other wise abort saving
        if(!$ok && $act == 'save') {
            $event->data = 'edit';
        }

        return false;
    }

    /**
     * Validate the given data
     *
     * Catches the Validation exceptions and transforms them into proper messages.
     *
     * Blank values are not validated and always pass
     *
     * @param AbstractBaseType $type
     * @param string $label
     * @param array|string|int $data
     * @return bool true if the data validates, otherwise false
     */
    protected function validate(AbstractBaseType $type, $label, $data) {
        $prefix = sprintf($this->getLang('validation_prefix'), $label);

        $ok = true;
        if(is_array($data)) {
            foreach($data as $value) {
                if(!blank($value)) {
                    try {
                        $type->validate($value);
                    } catch (ValidationException $e) {
                        msg($prefix . $e->getMessage(), -1);
                        $ok = false;
                    }
                }
            }
        } else {
            if(!blank($data)) {
                try {
                    $type->validate($data);
                } catch (ValidationException $e) {
                    msg($prefix . $e->getMessage(), -1);
                    $ok = false;
                }
            }
        }

        return $ok;
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
        global $INPUT;
        $schema = new SchemaData($tablename, $ID, $REV);
        $schemadata = $schema->getData();

        $structdata = $INPUT->arr(self::$VAR);
        if(isset($structdata[$tablename])) {
            $postdata = $structdata[$tablename];
        } else {
            $postdata = array();
        }

        $html = "<h3>$tablename</h3>";
        foreach($schemadata as $field) {
            $label = $field->getColumn()->getLabel();
            if(isset($postdata[$label])) {
                // posted data trumps stored data
                $field->setValue($postdata[$label]);
            }
            $trans = hsc($field->getColumn()->getTranslatedLabel());
            $name = self::$VAR . "[$tablename][$label]";
            $input = $field->getValueEditor($name);
            $element = "<label>$trans $input</label><br />";
            $html .= $element;
        }

        return $html;
    }

}

// vim:ts=4:sw=4:et:
