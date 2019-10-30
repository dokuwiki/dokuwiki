<?php
namespace dokuwiki\Form;

/**
 * Class LegacyForm
 *
 * Provides a compatibility layer to the old Doku_Form API
 *
 * This can be used to work with the modern API on forms provided by old events for
 * example. When you start new forms, just use Form\Form
 *
 * @package dokuwiki\Form
 */
class LegacyForm extends Form {

    /**
     * Creates a new modern form from an old legacy Doku_Form
     *
     * @param \Doku_Form $oldform
     */
    public function __construct(\Doku_Form $oldform) {
        parent::__construct($oldform->params);

        $this->hidden = $oldform->_hidden;

        foreach($oldform->_content as $element) {
            list($ctl, $attr) = $this->parseLegacyAttr($element);

            if(is_array($element)) {
                switch($ctl['elem']) {
                    case 'wikitext':
                        $this->addTextarea('wikitext')
                             ->attrs($attr)
                             ->id('wiki__text')
                             ->val($ctl['text'])
                             ->addClass($ctl['class']);
                        break;
                    case 'textfield':
                        $this->addTextInput($ctl['name'], $ctl['text'])
                             ->attrs($attr)
                             ->id($ctl['id'])
                             ->addClass($ctl['class']);
                        break;
                    case 'passwordfield':
                        $this->addPasswordInput($ctl['name'], $ctl['text'])
                             ->attrs($attr)
                             ->id($ctl['id'])
                             ->addClass($ctl['class']);
                        break;
                    case 'checkboxfield':
                        $this->addCheckbox($ctl['name'], $ctl['text'])
                             ->attrs($attr)
                             ->id($ctl['id'])
                             ->addClass($ctl['class']);
                        break;
                    case 'radiofield':
                        $this->addRadioButton($ctl['name'], $ctl['text'])
                             ->attrs($attr)
                             ->id($ctl['id'])
                             ->addClass($ctl['class']);
                        break;
                    case 'tag':
                        $this->addTag($ctl['tag'])
                             ->attrs($attr)
                             ->attr('name', $ctl['name'])
                             ->id($ctl['id'])
                             ->addClass($ctl['class']);
                        break;
                    case 'opentag':
                        $this->addTagOpen($ctl['tag'])
                             ->attrs($attr)
                             ->attr('name', $ctl['name'])
                             ->id($ctl['id'])
                             ->addClass($ctl['class']);
                        break;
                    case 'closetag':
                        $this->addTagClose($ctl['tag']);
                        break;
                    case 'openfieldset':
                        $this->addFieldsetOpen($ctl['legend'])
                            ->attrs($attr)
                            ->attr('name', $ctl['name'])
                            ->id($ctl['id'])
                            ->addClass($ctl['class']);
                        break;
                    case 'closefieldset':
                        $this->addFieldsetClose();
                        break;
                    case 'button':
                    case 'field':
                    case 'fieldright':
                    case 'filefield':
                    case 'menufield':
                    case 'listboxfield':
                        throw new \UnexpectedValueException('Unsupported legacy field ' . $ctl['elem']);
                        break;
                    default:
                        throw new \UnexpectedValueException('Unknown legacy field ' . $ctl['elem']);

                }
            } else {
                $this->addHTML($element);
            }
        }

    }

    /**
     * Parses out what is the elements attributes and what is control info
     *
     * @param array $legacy
     * @return array
     */
    protected function parseLegacyAttr($legacy) {
        $attributes = array();
        $control = array();

        foreach($legacy as $key => $val) {
            if($key[0] == '_') {
                $control[substr($key, 1)] = $val;
            } elseif($key == 'name') {
                $control[$key] = $val;
            } elseif($key == 'id') {
                $control[$key] = $val;
            } else {
                $attributes[$key] = $val;
            }
        }

        return array($control, $attributes);
    }

    /**
     * Translates our types to the legacy types
     *
     * @param string $type
     * @return string
     */
    protected function legacyType($type) {
        static $types = array(
            'text' => 'textfield',
            'password' => 'passwordfield',
            'checkbox' => 'checkboxfield',
            'radio' => 'radiofield',
            'tagopen' => 'opentag',
            'tagclose' => 'closetag',
            'fieldsetopen' => 'openfieldset',
            'fieldsetclose' => 'closefieldset',
        );
        if(isset($types[$type])) return $types[$type];
        return $type;
    }

    /**
     * Creates an old legacy form from this modern form's data
     *
     * @return \Doku_Form
     */
    public function toLegacy() {
        $this->balanceFieldsets();

        $legacy = new \Doku_Form($this->attrs());
        $legacy->_hidden = $this->hidden;
        foreach($this->elements as $element) {
            if(is_a($element, 'dokuwiki\Form\HTMLElement')) {
                $legacy->_content[] = $element->toHTML();
            } elseif(is_a($element, 'dokuwiki\Form\InputElement')) {
                /** @var InputElement $element */
                $data = $element->attrs();
                $data['_elem'] = $this->legacyType($element->getType());
                $label = $element->getLabel();
                if($label) {
                    $data['_class'] = $label->attr('class');
                }
                $legacy->_content[] = $data;
            }
        }

        return $legacy;
    }
}
