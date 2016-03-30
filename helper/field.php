<?php
use plugin\struct\meta\Column;
use plugin\struct\meta\Schema;
use plugin\struct\meta\StructException;
use plugin\struct\meta\Value;

/**
 * Allows adding a single struct field as a bureaucracy field
 *
 * This class is used when a field of the type struct_field is encountered in the
 * bureaucracy syntax.
 */
class helper_plugin_struct_field extends helper_plugin_bureaucracy_field {

    /** @var  Column */
    public $column;

    /**
     * Initialize the appropriate column
     *
     * @param array $args
     */
    public function initialize($args) {
        parent::initialize($args);

        // find the column
        try {
            $this->column = $this->findColumn($this->opt['label']);
        } catch(StructException $e) {
            msg(hsc($e->getMessage()), -1);
        }
    }

    /**
     * Validate the field
     *
     * @throws Exception
     */
    protected function _validate() {
        parent::_validate(); // checks optional state stuff
        if(!$this->column) return;
        $this->opt['value'] = $this->column->getType()->validate($this->opt['value']);
    }

    /**
     * Creates the HTML for the field
     *
     * @param array $params
     * @param Doku_Form $form
     * @param int $formid
     */
    public function renderfield($params, Doku_Form $form, $formid) {
        if(!$this->column) return;

        // this is what parent does
        $this->_handlePreload();
        if(!$form->_infieldset) {
            $form->startFieldset('');
        }
        if($this->error) {
            $params['class'] = 'bureaucracy_error';
        }

        // output the field
        $value = new Value($this->column, $this->opt['value']);
        $field = $this->makeField($value, $params['name']);
        $form->addElement($field);
    }



    /**
     * Create the input field
     *
     * @param Value $field
     * @param String $name field's name
     * @return string
     */
    protected function makeField(Value $field, $name) {
        $trans = hsc($field->getColumn()->getTranslatedLabel());
        $hint  = hsc($field->getColumn()->getTranslatedHint());
        $class = $hint ? 'hashint' : '';
        $lclass = $this->error ? 'bureaucracy_error' : '';
        $colname = $field->getColumn()->getFullQualifiedLabel();
        $required = ' <sup>*</sup>';

        $input = $field->getValueEditor($name);


        $html = '';
        $html .= "<label class=\"$lclass\" data-column=\"$colname\">";
        $html .= "<span class=\"label $class\" title=\"$hint\">$trans$required</span>";
        $html .= "<span class=\"input\">$input</span>";
        $html .= '</label>';

        return $html;
    }

    /**
     * Tries to find the correct column and schema
     *
     * @throws StructException
     * @param string $colname
     * @return \plugin\struct\meta\Column
     */
    protected function findColumn($colname) {
        list($table, $label) = explode('.', $colname, 2);
        if(!$table || !$label) {
            throw new StructException('Field \'%s\' not given in schema.field form', $colname);
        }
        $schema = new Schema($table);
        return $schema->findColumn($label);
    }

    /**
     * This ensures all language strings are still working
     *
     * @return string always 'bureaucracy'
     */
    public function getPluginName() {
        return 'bureaucracy';
    }

}
