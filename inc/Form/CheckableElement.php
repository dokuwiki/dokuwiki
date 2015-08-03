<?php
namespace dokuwiki\Form;

/**
 * Class CheckableElement
 *
 * For Radio- and Checkboxes
 *
 * @package DokuForm
 */
class CheckableElement extends InputElement {

    /**
     * @param string $type The type of this element
     * @param string $name The name of this form element
     * @param string $label The label text for this element
     */
    public function __construct($type, $name, $label) {
        parent::__construct($type, $name, $label);
        // default value is 1
        $this->attr('value', 1);
    }

    /**
     * Handles the useInput flag and sets the checked attribute accordingly
     */
    protected function prefillInput() {
        global $INPUT;
        list($name, $key) = $this->getInputName();
        $myvalue = $this->val();

        if(!$INPUT->has($name)) return;

        if($key === null) {
            // no key - single value
            $value = $INPUT->str($name);
            if($value == $myvalue) {
                $this->attr('checked', 'checked');
            } else {
                $this->rmattr('checked');
            }
        } else {
            // we have an array, there might be several values in it
            $input = $INPUT->arr($name);
            if(isset($input[$key])) {
                $this->rmattr('checked');

                // values seem to be in another sub array
                if(is_array($input[$key])) {
                    $input = $input[$key];
                }

                foreach($input as $value) {
                    if($value == $myvalue) {
                        $this->attr('checked', 'checked');
                    }
                }
            }
        }
    }

}
