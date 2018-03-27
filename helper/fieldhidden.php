<?php
/**
 * Allows adding a single struct field as a bureaucracy hidden field
 *
 * This class is used when a field of the type struct_fieldhidden is encountered in the
 * bureaucracy syntax.
 */
class helper_plugin_struct_fieldhidden extends helper_plugin_struct_field {
    /**
     * Render the field as XHTML
     *
     * Outputs the represented field using the passed Doku_Form object.
     *
     * @param array     $params Additional HTML specific parameters
     * @param Doku_Form $form   The target Doku_Form object
     * @param int       $formid unique identifier of the form which contains this field
     */
    function renderfield($params, Doku_Form $form, $formid) {
        $this->_handlePreload();
        $form->addHidden($params['name'], $this->getParam('value') . '');
    }
}
