<?php
namespace plugin\struct\types;

use dokuwiki\Form\Form;

class Text extends AbstractBaseType {

    protected $config = array(
        'prefix' => '',
        'postfix' => '',
    );

    /**
     * Adds the admin schema editor to the given form
     *
     * @param Form $form
     * @return void
     */
    public function schemaEditor(Form $form) {
        // TODO: Implement schemaEditor() method.
    }

    /**
     * Adds the frontend editor to the given form
     *
     * @param Form $form
     * @return void
     */
    public function frontendEditor(Form $form) {
        // TODO: Implement frontendEditor() method.
    }

    /**
     * Output the stored data
     *
     * @param int|string $value
     * @return string the HTML to represent this data
     */
    public function getDisplayData($value) {
        return hsc($this->config['prefix'] . $value . $this->config['postfix']);
    }
}
