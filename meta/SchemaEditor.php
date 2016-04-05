<?php

namespace plugin\struct\meta;

use dokuwiki\Form\Form;
use plugin\struct\types\Text;

if(!defined('JSON_PRETTY_PRINT')) define('JSON_PRETTY_PRINT', 0); // PHP 5.3 compatibility

/**
 * Class SchemaEditor
 *
 * Provides the editing interface for a given Schema as used in the admin backend. The actual modifying of the
 * schema happens in the SchemaBuilder class.
 *
 * @package plugin\struct\meta
 */
class SchemaEditor {
    /** @var Schema the schema that is edited */
    protected $schema;

    /** @var \DokuWiki_Plugin  */
    protected $hlp;

    /**
     * SchemaEditor constructor.
     * @param Schema $schema
     */
    public function __construct(Schema $schema) {
        $this->schema = $schema;
        $this->hlp = plugin_load('helper', 'struct_config');
    }

    /**
     * Returns the Admin Form to edit the schema
     *
     * This data is processed by the SchemaBuilder class
     *
     * @return string the HTML for the editor form
     * @see SchemaBuilder
     */
    public function getEditor() {
        $form = new Form(array('method' => 'POST', 'id'=>'plugin__struct_editor'));
        $form->setHiddenField('do', 'admin');
        $form->setHiddenField('page', 'struct_schemas');
        $form->setHiddenField('table', $this->schema->getTable());
        $form->setHiddenField('schema[id]', $this->schema->getId());

        $form->addHTML('<table class="inline">');
        $form->addHTML("<tr>
            <th>{$this->hlp->getLang('editor_sort')}</th>
            <th>{$this->hlp->getLang('editor_label')}</th>
            <th>{$this->hlp->getLang('editor_multi')}</th>
            <th>{$this->hlp->getLang('editor_conf')}</th>
            <th>{$this->hlp->getLang('editor_type')}</th>
            <th>{$this->hlp->getLang('editor_enabled')}</th>
        </tr>");


        foreach($this->schema->getColumns() as $key => $col) {
            $form->addHTML($this->adminColumn($col->getColref(), $col));
        }

        // FIXME new one needs to be added dynamically, this is just for testing
        $form->addHTML($this->adminColumn('new1', new Column($this->schema->getMaxsort()+10, new Text()), 'new'));

        $form->addHTML('</table>');
        $form->addButton('save', 'Save')->attr('type','submit');
        return $form->toHTML() . $this->initJSONEditor();
    }

    /**
     * Gives the code to attach the JSON editor to the config field
     *
     * We do not use the "normal" way, because this is rarely used code and there's no need to always load it.
     * @return string
     */
    protected function initJSONEditor() {
        $html = '';
        $html .= '<link href="'.DOKU_BASE.'lib/plugins/struct/jsoneditor/jsoneditor.min.css" rel="stylesheet" type="text/css">';
        $html .= '<link href="'.DOKU_BASE.'lib/plugins/struct/jsoneditor/setup.css" rel="stylesheet" type="text/css">';
        $html .= '<script src="'.DOKU_BASE.'lib/plugins/struct/jsoneditor/jsoneditor-minimalist.min.js"></script>';
        $html .= '<script src="'.DOKU_BASE.'lib/plugins/struct/jsoneditor/setup.js"></script>';
        return $html;
    }

    /**
     * Returns the HTML to edit a single column definition of the schema
     *
     * @param string $column_id
     * @param Column $col
     * @param string $key The key to use in the form
     * @return string
     * @todo this should probably be reused for adding new columns via AJAX later?
     */
    protected function adminColumn($column_id, Column $col, $key='cols') {
        $base = 'schema['.$key.'][' . $column_id . ']'; // base name for all fields

        $class = $col->isEnabled() ? '' : 'disabled';

        $html = "<tr class=\"$class\">";

        $html .= '<td class="sort">';
        $html .= '<input type="text" name="' . $base . '[sort]" value="' . hsc($col->getSort()) . '" size="3">';
        $html .= '</td>';

        $html .= '<td class="label">';
        $html .= '<input type="text" name="' . $base . '[label]" value="' . hsc($col->getType()->getLabel()) . '">';
        $html .= '</td>';

        $html .= '<td class="ismulti">';
        $checked = $col->getType()->isMulti() ? 'checked="checked"' : '';
        $html .= '<input type="checkbox" name="' . $base . '[ismulti]" value="1" ' . $checked . '>';
        $html .= '</td>';

        $html .= '<td class="config">';
        $config = json_encode($col->getType()->getConfig(), JSON_PRETTY_PRINT);
        $html .= '<textarea name="' . $base . '[config]" cols="45" rows="10" class="config">' . hsc($config) . '</textarea>';
        $html .= '</td>';

        $types = Column::allTypes();
        $html .= '<td class="class">';
        $html .= '<select name="' . $base . '[class]">';
        foreach($types as $type) {
            $selected = ($col->getType()->getClass() == $type) ? 'selected="selected"' : '';
            $html .= '<option value="' . hsc($type) . '" ' . $selected . '>' . hsc($type) . '</option>';
        }
        $html .= '</select>';
        $html .= '</td>';


        $html .= '<td class="isenabled">';
        $checked = $col->isEnabled() ? 'checked="checked"' : '';
        $html .= '<input type="checkbox" name="' . $base . '[isenabled]" value="1" ' . $checked . '>';
        $html .= '</td>';

        $html .= '</tr>';

        return $html;
    }

}
