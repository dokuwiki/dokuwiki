<?php
namespace dokuwiki\Form;

/**
 * Class FieldsetOpenElement
 *
 * Opens a Fieldset with an optional legend
 *
 * @package dokuwiki\Form
 */
class FieldsetOpenElement extends TagOpenElement {

    /**
     * @param string $legend
     * @param array $attributes
     */
    public function __construct($legend='', $attributes = array()) {
        // this is a bit messy and we just do it for the nicer class hierarchy
        // the parent would expect the tag in $value but we're storing the
        // legend there, so we have to set the type manually
        parent::__construct($legend, $attributes);
        $this->type = 'fieldsetopen';
    }

    /**
     * The HTML representation of this element
     *
     * @return string
     */
    public function toHTML() {
        $html = '<fieldset '.buildAttributes($this->attrs()).'>';
        $legend = $this->val();
        if($legend) $html .= DOKU_LF.'<legend>'.hsc($legend).'</legend>';
        return $html;
    }
}
