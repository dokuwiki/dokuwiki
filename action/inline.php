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
        if(substr($event->data, 0, $len) != 'plugin_struct_inline_') return;
        $event->preventDefault();
        $event->stopPropagation();

        if(substr($event->data, $len) == 'editor') {
            $this->inline_editor();
        }

        if(substr($event->data, $len) == 'save') {
            try {
                $this->inline_save();
            } catch(StructException $e) {
                http_status(500);
                header('Content-Type: text/plain; charset=utf-8');
                echo $e->getMessage();
            }
        }

        if(substr($event->data, $len) == 'cancel') {
            $this->inline_cancel();
        }
    }

    /**
     * Creates the inline editor
     */
    protected function inline_editor() {
        // silently fail when editing not possible
        if(!$this->initFromInput()) return;
        if(auth_quickaclcheck($this->pid) < AUTH_EDIT) return;
        if(checklock($this->pid)) return;

        // lock page
        lock($this->pid);

        // output the editor
        $value = $this->schemadata->getDataColumn($this->column);
        echo '<label data-column="'.hsc($this->column->getFullQualifiedLabel()).'">';
        echo $value->getValueEditor('entry');
        echo '</label>';
        $hint = $this->column->getType()->getTranslatedHint();
        if($hint) {
            echo '<div class="hint">';
            echo hsc($hint);
            echo '</div>';
        }

        // csrf protection
        formSecurityToken();
    }

    /**
     * Save the data posted by the inline editor
     */
    protected function inline_save() {
        global $INPUT;

        if(!$this->initFromInput()) {
            throw new StructException('inline save error: init');
        }
        // our own implementation of checkSecurityToken because we don't want the msg() call
        if(
            $INPUT->server->str('REMOTE_USER') &&
            getSecurityToken() != $INPUT->str('sectok')
        ) {
            throw new StructException('inline save error: csrf');
        }
        if(auth_quickaclcheck($this->pid) < AUTH_EDIT) {
            throw new StructException('inline save error: acl');
        }
        if(checklock($this->pid)) {
            throw new StructException('inline save error: lock');
        }

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

        // unlock
        unlock($this->pid);

        // reinit then render
        $this->initFromInput();
        $value = $this->schemadata->getDataColumn($this->column);
        $R = new Doku_Renderer_xhtml();
        $value->render($R, 'xhtml'); // FIXME use configured default renderer
        echo $R->doc;
    }

    /**
     * Unlock a page (on cancel action)
     */
    protected function inline_cancel() {
        global $INPUT;
        $pid = $INPUT->str('pid');
        unlock($pid);
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
