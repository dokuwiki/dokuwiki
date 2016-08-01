<?php
/**
 * DokuWiki Plugin struct (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Andreas Gohr, Michael Große <dokuwiki@cosmocode.de>
 */

// must be run within Dokuwiki
use dokuwiki\plugin\struct\meta\Column;
use dokuwiki\plugin\struct\meta\SchemaData;
use dokuwiki\plugin\struct\meta\StructException;
use dokuwiki\plugin\struct\meta\Title;
use dokuwiki\plugin\struct\meta\Validator;

if(!defined('DOKU_INC')) die();

/**
 * Class action_plugin_struct_inline
 *
 * Handle inline editing
 */
class action_plugin_struct_inline extends DokuWiki_Action_Plugin {

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
        $len = strlen('plugin_struct_inline_');
        if(substr($event->data, 0,  $len) != 'plugin_struct_inline_') return;
        $event->preventDefault();
        $event->stopPropagation();

        if(substr($event->data,$len) == 'editor') {
            $this->inline_editor();
        }

        if(substr($event->data,$len) == 'save') {
            try {
                $this->inline_save();
            } catch(StructException $e) {
                http_status(500);
                header('Content-Type: text/plain; charset=utf-8');
                echo $e->getMessage();
            }
        }
    }


    protected function inline_editor() {
        if(!$this->initFromInput()) return;


        // FIXME check read permission

        // FIXME lock page

        $value = $this->schemadata->getDataColumn($this->column);
        echo '<div>';
        echo $value->getValueEditor('entry');
        echo '</div>';

        $hint = $this->column->getType()->getTranslatedHint();
        if($hint) {
            echo '<div class="hint">';
            echo hsc($hint);
            echo '</div>';
        }
    }

    protected function inline_save() {
        global $INPUT;

        if(!$this->initFromInput()) {
            throw new StructException('inline save error');
        }

        // FIXME

        // FIXME handle CSRF protection
        // FIXME check write permission
        // FIXME make sure page still locked

        // validate
        $value = $INPUT->param('entry');
        $validator = new Validator();
        if(!$validator->validateValue($this->column, $value)) {
            throw new StructException(join("\n", $validator->getErrors()));
        }

        // current data
        $tosave = $this->schemadata->getDataArray();
        $tosave[$this->column->getLabel()] = $value;
        $tosave = array($this->schemadata->getTable() => $tosave);

        // save
        /** @var helper_plugin_struct $helper */
        $helper = plugin_load('helper', 'struct');
        $helper->saveData($this->pid, $tosave, 'inline edit');


        // reinit then render
        $this->initFromInput();
        $value = $this->schemadata->getDataColumn($this->column);
        $R = new Doku_Renderer_xhtml();
        $value->render($R, 'xhtml'); // FIXME use configured default renderer

        echo $R->doc;
    }

    /**
     * Initialize internal state based on input variables
     *
     * @return bool if initialization was successfull
     */
    protected function initFromInput() {
        global $INPUT;

        $this->schemadata = null;
        $this->column = null;

        $pid = $INPUT->str('pid');
        list($table, $field) = explode('.', $INPUT->str('field'));
        if(blank($pid)) return false;
        if(blank($table)) return false;
        if(blank($field)) return false;

        $this->pid = $pid;

        $this->schemadata = new SchemaData($table, $pid, 0);
        if(!$this->schemadata->getId()) {
            $this->schemadata = null;
            return false;
        }


        $this->column = $this->schemadata->findColumn($field);
        if(!$this->column || !$this->column->isVisibleInEditor()) {
            $this->schemadata = null;
            $this->column = null;
            return false;
        }

        return true;
    }

}
