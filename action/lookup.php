<?php
/**
 * DokuWiki Plugin struct (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Andreas Gohr, Michael Große <dokuwiki@cosmocode.de>
 */

// must be run within Dokuwiki
use dokuwiki\plugin\struct\meta\Column;
use dokuwiki\plugin\struct\meta\Schema;
use dokuwiki\plugin\struct\meta\SchemaData;
use dokuwiki\plugin\struct\meta\SchemaLookupData;
use dokuwiki\plugin\struct\meta\StructException;
use dokuwiki\plugin\struct\meta\Value;

if(!defined('DOKU_INC')) die();

/**
 * Class action_plugin_struct_lookup
 *
 * Handle lookup table editing
 */
class action_plugin_struct_lookup extends DokuWiki_Action_Plugin {

    /** @var  SchemaData */
    protected $schemadata = null;

    /** @var  Column */
    protected $column = null;

    /** @var String */
    protected $pid = '';

    /**
     * Registers a callback function for a given event
     *
     * @param Doku_Event_Handler $controller DokuWiki's event controller object
     * @return void
     */
    public function register(Doku_Event_Handler $controller) {
        $controller->register_hook('AJAX_CALL_UNKNOWN', 'BEFORE', $this, 'handle_ajax');
    }

    /**
     * @param Doku_Event $event
     * @param $param
     */
    public function handle_ajax(Doku_Event $event, $param) {
        $len = strlen('plugin_struct_lookup_');
        if(substr($event->data, 0, $len) != 'plugin_struct_lookup_') return;
        $event->preventDefault();
        $event->stopPropagation();

        try {

            if(substr($event->data, $len) == 'new') {
                $this->lookup_new();
            }

            if(substr($event->data, $len) == 'save') {
                $this->lookup_save();
            }

        } catch(StructException $e) {
            http_status(500);
            header('Content-Type: text/plain');
            echo $e->getMessage();
        }
    }

    protected function lookup_save() {
        global $INPUT;
        $tablename = $INPUT->str('schema');
        $data = $INPUT->arr('entry');

        $schemadata = new SchemaLookupData($tablename, 0, 0);
        $schemadata->saveData($data);
    }

    protected function lookup_new() {
        global $INPUT;
        global $lang;
        $tablename = $INPUT->str('schema');

        $schema = new Schema($tablename);

        echo '<div class="struct_entry_form">';
        echo '<fieldset>';
        echo '<legend>' . $this->getLang('lookup new entry') . '</legend>';
        /** @var action_plugin_struct_entry $entry */
        $entry = plugin_load('action', 'struct_entry');
        foreach($schema->getColumns(false) as $column) {
            $label = $column->getLabel();
            $field = new Value($column, '');
            echo $entry->makeField($field, "entry[$label]");
        }
        formSecurityToken(); // csrf protection
        echo '<input type="hidden" name="call" value="plugin_struct_lookup_save" />';
        echo '<input type="hidden" name="schema" value="' . hsc($tablename) . '" />';

        echo '<button type="submit">' . $lang['btn_save'] . '</button>';

        echo '</fieldset>';
        echo '</div>';

    }

}
