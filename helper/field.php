<?php
use dokuwiki\plugin\struct\meta\Column;
use dokuwiki\plugin\struct\meta\Schema;
use dokuwiki\plugin\struct\meta\StructException;
use dokuwiki\plugin\struct\meta\Value;
use dokuwiki\plugin\struct\meta\ValueValidator;

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
        $this->init($args);

        // find the column
        try {
            $this->column = $this->findColumn($this->opt['label']);
        } catch(StructException $e) {
            msg(hsc($e->getMessage()), -1);
        }

        $this->standardArgs($args);
    }

    /**
     * Sets the value and validates it
     *
     * @param mixed $value
     * @return bool value was set successfully validated
     */
    protected function setVal($value) {
        if(!$this->column) {
            $value = '';
        //don't validate placeholders here
        } elseif($this->replace($value) == $value) {
            $validator = new ValueValidator();
            $this->error = !$validator->validateValue($this->column, $value);
            if($this->error) {
                foreach($validator->getErrors() as $error) {
                    msg(hsc($error), -1);
                }
            }
        }

        if($value === array() || $value === '') {
            if(!isset($this->opt['optional'])) {
                $this->error = true;
                msg(sprintf($this->getLang('e_required'), hsc($this->opt['label'])), -1);
            }
        }

        $this->opt['value'] = $value;
        return !$this->error;
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
        if ($this->column->getType()->getClass() == 'Lookup') {
            $value->setValue($this->opt['value'], true);
        }
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
        $hint = hsc($field->getColumn()->getTranslatedHint());
        $class = $hint ? 'hashint' : '';
        $lclass = $this->error ? 'bureaucracy_error' : '';
        $colname = $field->getColumn()->getFullQualifiedLabel();
        $required = $this->opt['optional'] ? '' : ' <sup>*</sup>';

        $id = uniqid('struct__', false);
        $input = $field->getValueEditor($name, $id);

        $html = '<div class="field">';
        $html .= "<label class=\"$lclass\" data-column=\"$colname\" for=\"$id\">";
        $html .= "<span class=\"label $class\" title=\"$hint\">$trans$required</span>";
        $html .= '</label>';
        $html .= "<span class=\"input\">$input</span>";
        $html .= '</div>';

        return $html;
    }

    /**
     * Tries to find the correct column and schema
     *
     * @throws StructException
     * @param string $colname
     * @return \dokuwiki\plugin\struct\meta\Column
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
