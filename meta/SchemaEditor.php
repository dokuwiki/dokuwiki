<?php

namespace plugin\struct\meta;

use dokuwiki\Form\Form;
use plugin\struct\types\Text;

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

    /**
     * SchemaEditor constructor.
     * @param Schema $schema
     */
    public function __construct(Schema $schema) {
        $this->schema = $schema;
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
        $form = new Form(array('method' => 'POST'));
        $form->setHiddenField('do', 'admin');
        $form->setHiddenField('page', 'struct');
        $form->setHiddenField('table', $this->schema->getTable());
        $form->setHiddenField('schema[id]', $this->schema->getId());

        $form->addHTML('<table class="inline">');
        $form->addHTML('<tr><th>Sort</th><th>Label</th><th>Multi-Input?</th><th>Configuration</th><th>Type</th></tr>'); // FIXME localize

        foreach($this->schema->getColumns() as $key => $obj) {
            $form->addHTML($this->adminColumn($key, $obj));
        }

        // FIXME new one needs to be added dynamically, this is just for testing
        $form->addHTML($this->adminColumn('new1', new Column($this->schema->getMaxsort()+10, new Text()), 'new'));

        $form->addHTML('</table>');
        $form->addButton('save', 'Save')->attr('type','submit');
        return $form->toHTML();
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

        $html = '<tr>';

        $html .= '<td>';
        $html .= '<input type="text" name="' . $base . '[sort]" value="' . hsc($col->getSort()) . '" size="3">';
        $html .= '</td>';

        $html .= '<td>';
        $html .= '<input type="text" name="' . $base . '[label]" value="' . hsc($col->getType()->getLabel()) . '">';
        $html .= '</td>';

        $html .= '<td>';
        $checked = $col->getType()->isMulti() ? 'checked="checked"' : '';
        $html .= '<input type="checkbox" name="' . $base . '[ismulti]" value="1" ' . $checked . '>';
        $html .= '</td>';

        $html .= '<td>';
        $config = json_encode($col->getType()->getConfig(), JSON_PRETTY_PRINT);
        $html .= '<textarea name="' . $base . '[config]" cols="45" rows="10">' . hsc($config) . '</textarea>';
        $html .= '</td>';

        $types = Column::allTypes();
        $html .= '<td>';
        $html .= '<select name="' . $base . '[class]">';
        foreach($types as $type) {
            $selected = ($col->getType()->getClass() == $type) ? 'selected="selected"' : '';
            $html .= '<option value="' . hsc($type) . '" ' . $selected . '>' . hsc($type) . '</option>';
        }
        $html .= '</select>';
        $html .= '</td>';

        $html .= '</tr>';

        return $html;
    }

}
