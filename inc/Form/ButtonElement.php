<?php
namespace dokuwiki\Form;

/**
 * Class ButtonElement
 *
 * Represents a simple button
 *
 * @package dokuwiki\Form
 */
class ButtonElement extends Element {

    /** @var string HTML content */
    protected $content = '';

    /**
     * @param string $name
     * @param string $content HTML content of the button. You have to escape it yourself.
     */
    function __construct($name, $content = '') {
        parent::__construct('button', array('name' => $name, 'value' => 1));
        $this->content = $content;
    }

    /**
     * The HTML representation of this element
     *
     * @return string
     */
    public function toHTML() {
        return '<button ' . buildAttributes($this->attrs(), true) . '>'.$this->content.'</button>';
    }

}
