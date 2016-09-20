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
use dokuwiki\plugin\struct\meta\Value;

/**
 * Class action_plugin_struct_entry
 *
 * Handles adding struct forms to the default editor
 */
class action_plugin_struct_edit extends DokuWiki_Action_Plugin {

    /**
     * @var string The form name we use to transfer schema data
     */
    protected static $VAR = 'struct_schema_data';

    /**
     * Registers a callback function for a given event
     *
     * @param Doku_Event_Handler $controller DokuWiki's event controller object
     * @return void
     */
    public function register(Doku_Event_Handler $controller) {
        // add the struct editor to the edit form;
        $controller->register_hook('HTML_EDITFORM_OUTPUT', 'BEFORE', $this, 'handle_editform');
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
     * Create the form to edit schemadata
     *
     * @param string $tablename
     * @return string The HTML for this schema's form
     */
    protected function createForm($tablename) {
        global $ID;
        global $REV;
        global $INPUT;
        if(auth_quickaclcheck($ID) == AUTH_READ) return '';
        if(checklock($ID)) return '';
        $schema = AccessTable::byTableName($tablename, $ID, $REV);
        $schemadata = $schema->getData();

        $structdata = $INPUT->arr(self::$VAR);
        if(isset($structdata[$tablename])) {
            $postdata = $structdata[$tablename];
        } else {
            $postdata = array();
        }

        // we need a short, unique identifier to use in the cookie. this should be good enough
        $schemaid = 'SRCT' . substr(str_replace(array('+', '/'), '', base64_encode(sha1($tablename, true))), 0, 5);
        $html = '<fieldset data-schema="' . $schemaid . '">';
        $html .= '<legend>' . hsc($tablename) . '</legend>';
        foreach($schemadata as $field) {
            $label = $field->getColumn()->getLabel();
            if(isset($postdata[$label])) {
                // posted data trumps stored data
                $field->setValue($postdata[$label], true);
            }
            $html .= $this->makeField($field, self::$VAR . "[$tablename][$label]");
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
        $hint = hsc($field->getColumn()->getTranslatedHint());
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
