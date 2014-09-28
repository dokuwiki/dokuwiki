<?php
/**
 * DokuWiki XHTML Form
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Tom N Harris <tnharris@whoopdedo.org>
 */

if(!defined('DOKU_INC')) die('meh.');

/**
 * Class for creating simple HTML forms.
 *
 * The forms is built from a list of pseudo-tags (arrays with expected keys).
 * Every pseudo-tag must have the key '_elem' set to the name of the element.
 * When printed, the form class calls functions named 'form_$type' for each
 * element it contains.
 *
 * Standard practice is for non-attribute keys in a pseudo-element to start
 * with '_'. Other keys are HTML attributes that will be included in the element
 * tag. That way, the element output functions can pass the pseudo-element
 * directly to buildAttributes.
 *
 * See the form_make* functions later in this file.
 *
 * @author Tom N Harris <tnharris@whoopdedo.org>
 */
class Doku_Form {

    // Form id attribute
    var $params = array();

    // Draw a border around form fields.
    // Adds <fieldset></fieldset> around the elements
    var $_infieldset = false;

    // Hidden form fields.
    var $_hidden = array();

    // Array of pseudo-tags
    var $_content = array();

    /**
     * Constructor
     *
     * Sets parameters and autoadds a security token. The old calling convention
     * with up to four parameters is deprecated, instead the first parameter
     * should be an array with parameters.
     *
     * @param mixed       $params  Parameters for the HTML form element; Using the deprecated
     *                             calling convention this is the ID attribute of the form
     * @param bool|string $action  (optional, deprecated) submit URL, defaults to current page
     * @param bool|string $method  (optional, deprecated) 'POST' or 'GET', default is POST
     * @param bool|string $enctype (optional, deprecated) Encoding type of the data
     * @author  Tom N Harris <tnharris@whoopdedo.org>
     */
    function Doku_Form($params, $action=false, $method=false, $enctype=false) {
        if(!is_array($params)) {
            $this->params = array('id' => $params);
            if ($action !== false) $this->params['action'] = $action;
            if ($method !== false) $this->params['method'] = strtolower($method);
            if ($enctype !== false) $this->params['enctype'] = $enctype;
        } else {
            $this->params = $params;
        }

        if (!isset($this->params['method'])) {
            $this->params['method'] = 'post';
        } else {
            $this->params['method'] = strtolower($this->params['method']);
        }

        if (!isset($this->params['action'])) {
            $this->params['action'] = '';
        }

        $this->addHidden('sectok', getSecurityToken());
    }

    /**
     * startFieldset
     *
     * Add <fieldset></fieldset> tags around fields.
     * Usually results in a border drawn around the form.
     *
     * @param   string  $legend Label that will be printed with the border.
     * @author  Tom N Harris <tnharris@whoopdedo.org>
     */
    function startFieldset($legend) {
        if ($this->_infieldset) {
            $this->addElement(array('_elem'=>'closefieldset'));
        }
        $this->addElement(array('_elem'=>'openfieldset', '_legend'=>$legend));
        $this->_infieldset = true;
    }

    /**
     * endFieldset
     *
     * @author  Tom N Harris <tnharris@whoopdedo.org>
     */
    function endFieldset() {
        if ($this->_infieldset) {
            $this->addElement(array('_elem'=>'closefieldset'));
        }
        $this->_infieldset = false;
    }

    /**
     * addHidden
     *
     * Adds a name/value pair as a hidden field.
     * The value of the field (but not the name) will be passed to
     * formText() before printing.
     *
     * @param   string  $name   Field name.
     * @param   string  $value  Field value. If null, remove a previously added field.
     * @author  Tom N Harris <tnharris@whoopdedo.org>
     */
    function addHidden($name, $value) {
        if (is_null($value))
            unset($this->_hidden[$name]);
        else
            $this->_hidden[$name] = $value;
    }

    /**
     * addElement
     *
     * Appends a content element to the form.
     * The element can be either a pseudo-tag or string.
     * If string, it is printed without escaping special chars.   *
     *
     * @param   string|array  $elem   Pseudo-tag or string to add to the form.
     * @author  Tom N Harris <tnharris@whoopdedo.org>
     */
    function addElement($elem) {
        $this->_content[] = $elem;
    }

    /**
     * insertElement
     *
     * Inserts a content element at a position.
     *
     * @param   string       $pos  0-based index where the element will be inserted.
     * @param   string|array $elem Pseudo-tag or string to add to the form.
     * @author  Tom N Harris <tnharris@whoopdedo.org>
     */
    function insertElement($pos, $elem) {
        array_splice($this->_content, $pos, 0, array($elem));
    }

    /**
     * replaceElement
     *
     * Replace with NULL to remove an element.
     *
     * @param   int          $pos  0-based index the element will be placed at.
     * @param   string|array $elem Pseudo-tag or string to add to the form.
     * @author  Tom N Harris <tnharris@whoopdedo.org>
     */
    function replaceElement($pos, $elem) {
        $rep = array();
        if (!is_null($elem)) $rep[] = $elem;
        array_splice($this->_content, $pos, 1, $rep);
    }

    /**
     * findElementByType
     *
     * Gets the position of the first of a type of element.
     *
     * @param   string  $type   Element type to look for.
     * @return  int     position of element if found, otherwise false
     * @author  Tom N Harris <tnharris@whoopdedo.org>
     */
    function findElementByType($type) {
        foreach ($this->_content as $pos=>$elem) {
            if (is_array($elem) && $elem['_elem'] == $type)
                return $pos;
        }
        return false;
    }

    /**
     * findElementById
     *
     * Gets the position of the element with an ID attribute.
     *
     * @param   string  $id     ID of the element to find.
     * @return  int     position of element if found, otherwise false
     * @author  Tom N Harris <tnharris@whoopdedo.org>
     */
    function findElementById($id) {
        foreach ($this->_content as $pos=>$elem) {
            if (is_array($elem) && isset($elem['id']) && $elem['id'] == $id)
                return $pos;
        }
        return false;
    }

    /**
     * findElementByAttribute
     *
     * Gets the position of the first element with a matching attribute value.
     *
     * @param   string  $name   Attribute name.
     * @param   string  $value  Attribute value.
     * @return  int     position of element if found, otherwise false
     * @author  Tom N Harris <tnharris@whoopdedo.org>
     */
    function findElementByAttribute($name, $value) {
        foreach ($this->_content as $pos=>$elem) {
            if (is_array($elem) && isset($elem[$name]) && $elem[$name] == $value)
                return $pos;
        }
        return false;
    }

    /**
     * getElementAt
     *
     * Returns a reference to the element at a position.
     * A position out-of-bounds will return either the
     * first (underflow) or last (overflow) element.
     *
     * @param   int     $pos    0-based index
     * @return  array reference  pseudo-element
     * @author  Tom N Harris <tnharris@whoopdedo.org>
     */
    function &getElementAt($pos) {
        if ($pos < 0) $pos = count($this->_content) + $pos;
        if ($pos < 0) $pos = 0;
        if ($pos >= count($this->_content)) $pos = count($this->_content) - 1;
        return $this->_content[$pos];
    }

    /**
     * Return the assembled HTML for the form.
     *
     * Each element in the form will be passed to a function named
     * 'form_$type'. The function should return the HTML to be printed.
     *
     * @author  Tom N Harris <tnharris@whoopdedo.org>
     */
    function getForm() {
        global $lang;
        $form = '';
        $this->params['accept-charset'] = $lang['encoding'];
        $form .= '<form ' . buildAttributes($this->params,false) . '><div class="no">' . DOKU_LF;
        if (!empty($this->_hidden)) {
            foreach ($this->_hidden as $name=>$value)
                $form .= form_hidden(array('name'=>$name, 'value'=>$value));
        }
        foreach ($this->_content as $element) {
            if (is_array($element)) {
                $elem_type = $element['_elem'];
                if (function_exists('form_'.$elem_type)) {
                    $form .= call_user_func('form_'.$elem_type, $element).DOKU_LF;
                }
            } else {
                $form .= $element;
            }
        }
        if ($this->_infieldset) $form .= form_closefieldset().DOKU_LF;
        $form .= '</div></form>'.DOKU_LF;

        return $form;
    }

    /**
     * Print the assembled form
     *
     * wraps around getForm()
     */
    function printForm(){
        echo $this->getForm();
    }

    /**
     * Add a radio set
     *
     * This function adds a set of radio buttons to the form. If $_POST[$name]
     * is set, this radio is preselected, else the first radio button.
     *
     * @param string    $name    The HTML field name
     * @param array     $entries An array of entries $value => $caption
     *
     * @author Adrian Lang <lang@cosmocode.de>
     */

    function addRadioSet($name, $entries) {
        global $INPUT;
        $value = (array_key_exists($INPUT->post->str($name), $entries)) ?
                 $INPUT->str($name) : key($entries);
        foreach($entries as $val => $cap) {
            $data = ($value === $val) ? array('checked' => 'checked') : array();
            $this->addElement(form_makeRadioField($name, $val, $cap, '', '', $data));
        }
    }

}

/**
 * form_makeTag
 *
 * Create a form element for a non-specific empty tag.
 *
 * @param   string  $tag    Tag name.
 * @param   array   $attrs  Optional attributes.
 * @return  array   pseudo-tag
 * @author  Tom N Harris <tnharris@whoopdedo.org>
 */
function form_makeTag($tag, $attrs=array()) {
    $elem = array('_elem'=>'tag', '_tag'=>$tag);
    return array_merge($elem, $attrs);
}

/**
 * form_makeOpenTag
 *
 * Create a form element for a non-specific opening tag.
 * Remember to put a matching close tag after this as well.
 *
 * @param   string  $tag    Tag name.
 * @param   array   $attrs  Optional attributes.
 * @return  array   pseudo-tag
 * @author  Tom N Harris <tnharris@whoopdedo.org>
 */
function form_makeOpenTag($tag, $attrs=array()) {
    $elem = array('_elem'=>'opentag', '_tag'=>$tag);
    return array_merge($elem, $attrs);
}

/**
 * form_makeCloseTag
 *
 * Create a form element for a non-specific closing tag.
 * Careless use of this will result in invalid XHTML.
 *
 * @param   string  $tag    Tag name.
 * @return  array   pseudo-tag
 * @author  Tom N Harris <tnharris@whoopdedo.org>
 */
function form_makeCloseTag($tag) {
    return array('_elem'=>'closetag', '_tag'=>$tag);
}

/**
 * form_makeWikiText
 *
 * Create a form element for a textarea containing wiki text.
 * Only one wikitext element is allowed on a page. It will have
 * a name of 'wikitext' and id 'wiki__text'. The text will
 * be passed to formText() before printing.
 *
 * @param   string  $text   Text to fill the field with.
 * @param   array   $attrs  Optional attributes.
 * @return  array   pseudo-tag
 * @author  Tom N Harris <tnharris@whoopdedo.org>
 */
function form_makeWikiText($text, $attrs=array()) {
    $elem = array('_elem'=>'wikitext', '_text'=>$text,
                        'class'=>'edit', 'cols'=>'80', 'rows'=>'10');
    return array_merge($elem, $attrs);
}

/**
 * form_makeButton
 *
 * Create a form element for an action button.
 * A title will automatically be generated using the value and
 * accesskey attributes, unless you provide one.
 *
 * @param   string  $type   Type attribute. 'submit' or 'cancel'
 * @param   string  $act    Wiki action of the button, will be used as the do= parameter
 * @param   string  $value  (optional) Displayed label. Uses $act if not provided.
 * @param   array   $attrs  Optional attributes.
 * @return  array   pseudo-tag
 * @author  Tom N Harris <tnharris@whoopdedo.org>
 */
function form_makeButton($type, $act, $value='', $attrs=array()) {
    if ($value == '') $value = $act;
    $elem = array('_elem'=>'button', 'type'=>$type, '_action'=>$act,
                        'value'=>$value, 'class'=>'button');
    if (!empty($attrs['accesskey']) && empty($attrs['title'])) {
        $attrs['title'] = $value . ' ['.strtoupper($attrs['accesskey']).']';
    }
    return array_merge($elem, $attrs);
}

/**
 * form_makeField
 *
 * Create a form element for a labelled input element.
 * The label text will be printed before the input.
 *
 * @param   string  $type   Type attribute of input.
 * @param   string  $name   Name attribute of the input.
 * @param   string  $value  (optional) Default value.
 * @param   string  $class  Class attribute of the label. If this is 'block',
 *                          then a line break will be added after the field.
 * @param   string  $label  Label that will be printed before the input.
 * @param   string  $id     ID attribute of the input. If set, the label will
 *                          reference it with a 'for' attribute.
 * @param   array   $attrs  Optional attributes.
 * @return  array   pseudo-tag
 * @author  Tom N Harris <tnharris@whoopdedo.org>
 */
function form_makeField($type, $name, $value='', $label=null, $id='', $class='', $attrs=array()) {
    if (is_null($label)) $label = $name;
    $elem = array('_elem'=>'field', '_text'=>$label, '_class'=>$class,
                        'type'=>$type, 'id'=>$id, 'name'=>$name, 'value'=>$value);
    return array_merge($elem, $attrs);
}

/**
 * form_makeFieldRight
 *
 * Create a form element for a labelled input element.
 * The label text will be printed after the input.
 *
 * @see     form_makeField
 * @author  Tom N Harris <tnharris@whoopdedo.org>
 */
function form_makeFieldRight($type, $name, $value='', $label=null, $id='', $class='', $attrs=array()) {
    if (is_null($label)) $label = $name;
    $elem = array('_elem'=>'fieldright', '_text'=>$label, '_class'=>$class,
                        'type'=>$type, 'id'=>$id, 'name'=>$name, 'value'=>$value);
    return array_merge($elem, $attrs);
}

/**
 * form_makeTextField
 *
 * Create a form element for a text input element with label.
 *
 * @see     form_makeField
 * @author  Tom N Harris <tnharris@whoopdedo.org>
 */
function form_makeTextField($name, $value='', $label=null, $id='', $class='', $attrs=array()) {
    if (is_null($label)) $label = $name;
    $elem = array('_elem'=>'textfield', '_text'=>$label, '_class'=>$class,
                        'id'=>$id, 'name'=>$name, 'value'=>$value, 'class'=>'edit');
    return array_merge($elem, $attrs);
}

/**
 * form_makePasswordField
 *
 * Create a form element for a password input element with label.
 * Password elements have no default value, for obvious reasons.
 *
 * @see     form_makeField
 * @author  Tom N Harris <tnharris@whoopdedo.org>
 */
function form_makePasswordField($name, $label=null, $id='', $class='', $attrs=array()) {
    if (is_null($label)) $label = $name;
    $elem = array('_elem'=>'passwordfield', '_text'=>$label, '_class'=>$class,
                        'id'=>$id, 'name'=>$name, 'class'=>'edit');
    return array_merge($elem, $attrs);
}

/**
 * form_makeFileField
 *
 * Create a form element for a file input element with label
 *
 * @see     form_makeField
 * @author  Michael Klier <chi@chimeric.de>
 */
function form_makeFileField($name, $label=null, $id='', $class='', $attrs=array()) {
    if (is_null($label)) $label = $name;
    $elem = array('_elem'=>'filefield', '_text'=>$label, '_class'=>$class,
                        'id'=>$id, 'name'=>$name, 'class'=>'edit');
    return array_merge($elem, $attrs);
}

/**
 * form_makeCheckboxField
 *
 * Create a form element for a checkbox input element with label.
 * If $value is an array, a hidden field with the same name and the value
 * $value[1] is constructed as well.
 *
 * @see     form_makeFieldRight
 * @author  Tom N Harris <tnharris@whoopdedo.org>
 */
function form_makeCheckboxField($name, $value='1', $label=null, $id='', $class='', $attrs=array()) {
    if (is_null($label)) $label = $name;
    if (is_null($value) || $value=='') $value='0';
    $elem = array('_elem'=>'checkboxfield', '_text'=>$label, '_class'=>$class,
                        'id'=>$id, 'name'=>$name, 'value'=>$value);
    return array_merge($elem, $attrs);
}

/**
 * form_makeRadioField
 *
 * Create a form element for a radio button input element with label.
 *
 * @see     form_makeFieldRight
 * @author  Tom N Harris <tnharris@whoopdedo.org>
 */
function form_makeRadioField($name, $value='1', $label=null, $id='', $class='', $attrs=array()) {
    if (is_null($label)) $label = $name;
    if (is_null($value) || $value=='') $value='0';
    $elem = array('_elem'=>'radiofield', '_text'=>$label, '_class'=>$class,
                        'id'=>$id, 'name'=>$name, 'value'=>$value);
    return array_merge($elem, $attrs);
}

/**
 * form_makeMenuField
 *
 * Create a form element for a drop-down menu with label.
 * The list of values can be strings, arrays of (value,text),
 * or an associative array with the values as keys and labels as values.
 * An item is selected by supplying its value or integer index.
 * If the list of values is an associative array, the selected item must be
 * a string.
 *
 * @author  Tom N Harris <tnharris@whoopdedo.org>
 */
function form_makeMenuField($name, $values, $selected='', $label=null, $id='', $class='', $attrs=array()) {
    if (is_null($label)) $label = $name;
    $options = array();
    reset($values);
    // FIXME: php doesn't know the difference between a string and an integer
    if (is_string(key($values))) {
        foreach ($values as $val=>$text) {
            $options[] = array($val,$text, (!is_null($selected) && $val==$selected));
        }
    } else {
        if (is_integer($selected)) $selected = $values[$selected];
        foreach ($values as $val) {
            if (is_array($val))
                @list($val,$text) = $val;
            else
                $text = null;
            $options[] = array($val,$text,$val===$selected);
        }
    }
    $elem = array('_elem'=>'menufield', '_options'=>$options, '_text'=>$label, '_class'=>$class,
                        'id'=>$id, 'name'=>$name);
    return array_merge($elem, $attrs);
}

/**
 * form_makeListboxField
 *
 * Create a form element for a list box with label.
 * The list of values can be strings, arrays of (value,text),
 * or an associative array with the values as keys and labels as values.
 * Items are selected by supplying its value or an array of values.
 *
 * @author  Tom N Harris <tnharris@whoopdedo.org>
 */
function form_makeListboxField($name, $values, $selected='', $label=null, $id='', $class='', $attrs=array()) {
    if (is_null($label)) $label = $name;
    $options = array();
    reset($values);
    if (is_null($selected) || $selected == '') {
        $selected = array();
    } elseif (!is_array($selected)) {
        $selected = array($selected);
    }
    // FIXME: php doesn't know the difference between a string and an integer
    if (is_string(key($values))) {
        foreach ($values as $val=>$text) {
            $options[] = array($val,$text,in_array($val,$selected));
        }
    } else {
        foreach ($values as $val) {
            $disabled = false;
            if (is_array($val)) {
                @list($val,$text,$disabled) = $val;
            } else {
                $text = null;
            }
            $options[] = array($val,$text,in_array($val,$selected),$disabled);
        }
    }
    $elem = array('_elem'=>'listboxfield', '_options'=>$options, '_text'=>$label, '_class'=>$class,
                        'id'=>$id, 'name'=>$name);
    return array_merge($elem, $attrs);
}

/**
 * form_tag
 *
 * Print the HTML for a generic empty tag.
 * Requires '_tag' key with name of the tag.
 * Attributes are passed to buildAttributes()
 *
 * @author  Tom N Harris <tnharris@whoopdedo.org>
 */
function form_tag($attrs) {
    return '<'.$attrs['_tag'].' '.buildAttributes($attrs,true).'/>';
}

/**
 * form_opentag
 *
 * Print the HTML for a generic opening tag.
 * Requires '_tag' key with name of the tag.
 * Attributes are passed to buildAttributes()
 *
 * @author  Tom N Harris <tnharris@whoopdedo.org>
 */
function form_opentag($attrs) {
    return '<'.$attrs['_tag'].' '.buildAttributes($attrs,true).'>';
}

/**
 * form_closetag
 *
 * Print the HTML for a generic closing tag.
 * Requires '_tag' key with name of the tag.
 * There are no attributes.
 *
 * @author  Tom N Harris <tnharris@whoopdedo.org>
 */
function form_closetag($attrs) {
    return '</'.$attrs['_tag'].'>';
}

/**
 * form_openfieldset
 *
 * Print the HTML for an opening fieldset tag.
 * Uses the '_legend' key.
 * Attributes are passed to buildAttributes()
 *
 * @author  Tom N Harris <tnharris@whoopdedo.org>
 */
function form_openfieldset($attrs) {
    $s = '<fieldset '.buildAttributes($attrs,true).'>';
    if (!is_null($attrs['_legend'])) $s .= '<legend>'.$attrs['_legend'].'</legend>';
    return $s;
}

/**
 * form_closefieldset
 *
 * Print the HTML for a closing fieldset tag.
 * There are no attributes.
 *
 * @author  Tom N Harris <tnharris@whoopdedo.org>
 */
function form_closefieldset() {
    return '</fieldset>';
}

/**
 * form_hidden
 *
 * Print the HTML for a hidden input element.
 * Uses only 'name' and 'value' attributes.
 * Value is passed to formText()
 *
 * @author  Tom N Harris <tnharris@whoopdedo.org>
 */
function form_hidden($attrs) {
    return '<input type="hidden" name="'.$attrs['name'].'" value="'.formText($attrs['value']).'" />';
}

/**
 * form_wikitext
 *
 * Print the HTML for the wiki textarea.
 * Requires '_text' with default text of the field.
 * Text will be passed to formText(), attributes to buildAttributes()
 *
 * @author  Tom N Harris <tnharris@whoopdedo.org>
 */
function form_wikitext($attrs) {
    // mandatory attributes
    unset($attrs['name']);
    unset($attrs['id']);
    return '<textarea name="wikitext" id="wiki__text" dir="auto" '
                 .buildAttributes($attrs,true).'>'.DOKU_LF
                 .formText($attrs['_text'])
                 .'</textarea>';
}

/**
 * form_button
 *
 * Print the HTML for a form button.
 * If '_action' is set, the button name will be "do[_action]".
 * Other attributes are passed to buildAttributes()
 *
 * @author  Tom N Harris <tnharris@whoopdedo.org>
 */
function form_button($attrs) {
    $p = (!empty($attrs['_action'])) ? 'name="do['.$attrs['_action'].']" ' : '';
    return '<input '.$p.buildAttributes($attrs,true).' />';
}

/**
 * form_field
 *
 * Print the HTML for a form input field.
 *   _class : class attribute used on the label tag
 *   _text  : Text to display before the input. Not escaped.
 * Other attributes are passed to buildAttributes() for the input tag.
 *
 * @author  Tom N Harris <tnharris@whoopdedo.org>
 */
function form_field($attrs) {
    $s = '<label';
    if ($attrs['_class']) $s .= ' class="'.$attrs['_class'].'"';
    if (!empty($attrs['id'])) $s .= ' for="'.$attrs['id'].'"';
    $s .= '><span>'.$attrs['_text'].'</span>';
    $s .= ' <input '.buildAttributes($attrs,true).' /></label>';
    if (preg_match('/(^| )block($| )/', $attrs['_class']))
        $s .= '<br />';
    return $s;
}

/**
 * form_fieldright
 *
 * Print the HTML for a form input field. (right-aligned)
 *   _class : class attribute used on the label tag
 *   _text  : Text to display after the input. Not escaped.
 * Other attributes are passed to buildAttributes() for the input tag.
 *
 * @author  Tom N Harris <tnharris@whoopdedo.org>
 */
function form_fieldright($attrs) {
    $s = '<label';
    if ($attrs['_class']) $s .= ' class="'.$attrs['_class'].'"';
    if (!empty($attrs['id'])) $s .= ' for="'.$attrs['id'].'"';
    $s .= '><input '.buildAttributes($attrs,true).' />';
    $s .= ' <span>'.$attrs['_text'].'</span></label>';
    if (preg_match('/(^| )block($| )/', $attrs['_class']))
        $s .= '<br />';
    return $s;
}

/**
 * form_textfield
 *
 * Print the HTML for a text input field.
 *   _class : class attribute used on the label tag
 *   _text  : Text to display before the input. Not escaped.
 * Other attributes are passed to buildAttributes() for the input tag.
 *
 * @author  Tom N Harris <tnharris@whoopdedo.org>
 */
function form_textfield($attrs) {
    // mandatory attributes
    unset($attrs['type']);
    $s = '<label';
    if ($attrs['_class']) $s .= ' class="'.$attrs['_class'].'"';
    if (!empty($attrs['id'])) $s .= ' for="'.$attrs['id'].'"';
    $s .= '><span>'.$attrs['_text'].'</span> ';
    $s .= '<input type="text" '.buildAttributes($attrs,true).' /></label>';
    if (preg_match('/(^| )block($| )/', $attrs['_class']))
        $s .= '<br />';
    return $s;
}

/**
 * form_passwordfield
 *
 * Print the HTML for a password input field.
 *   _class : class attribute used on the label tag
 *   _text  : Text to display before the input. Not escaped.
 * Other attributes are passed to buildAttributes() for the input tag.
 *
 * @author  Tom N Harris <tnharris@whoopdedo.org>
 */
function form_passwordfield($attrs) {
    // mandatory attributes
    unset($attrs['type']);
    $s = '<label';
    if ($attrs['_class']) $s .= ' class="'.$attrs['_class'].'"';
    if (!empty($attrs['id'])) $s .= ' for="'.$attrs['id'].'"';
    $s .= '><span>'.$attrs['_text'].'</span> ';
    $s .= '<input type="password" '.buildAttributes($attrs,true).' /></label>';
    if (preg_match('/(^| )block($| )/', $attrs['_class']))
        $s .= '<br />';
    return $s;
}

/**
 * form_filefield
 *
 * Print the HTML for a file input field.
 *   _class     : class attribute used on the label tag
 *   _text      : Text to display before the input. Not escaped
 *   _maxlength : Allowed size in byte
 *   _accept    : Accepted mime-type
 * Other attributes are passed to buildAttributes() for the input tag
 *
 * @author  Michael Klier <chi@chimeric.de>
 */
function form_filefield($attrs) {
    $s = '<label';
    if ($attrs['_class']) $s .= ' class="'.$attrs['_class'].'"';
    if (!empty($attrs['id'])) $s .= ' for="'.$attrs['id'].'"';
    $s .= '><span>'.$attrs['_text'].'</span> ';
    $s .= '<input type="file" '.buildAttributes($attrs,true);
    if (!empty($attrs['_maxlength'])) $s .= ' maxlength="'.$attrs['_maxlength'].'"';
    if (!empty($attrs['_accept'])) $s .= ' accept="'.$attrs['_accept'].'"';
    $s .= ' /></label>';
    if (preg_match('/(^| )block($| )/', $attrs['_class']))
        $s .= '<br />';
    return $s;
}

/**
 * form_checkboxfield
 *
 * Print the HTML for a checkbox input field.
 *   _class : class attribute used on the label tag
 *   _text  : Text to display after the input. Not escaped.
 * Other attributes are passed to buildAttributes() for the input tag.
 * If value is an array, a hidden field with the same name and the value
 * $attrs['value'][1] is constructed as well.
 *
 * @author  Tom N Harris <tnharris@whoopdedo.org>
 */
function form_checkboxfield($attrs) {
    // mandatory attributes
    unset($attrs['type']);
    $s = '<label';
    if ($attrs['_class']) $s .= ' class="'.$attrs['_class'].'"';
    if (!empty($attrs['id'])) $s .= ' for="'.$attrs['id'].'"';
    $s .= '>';
    if (is_array($attrs['value'])) {
        echo '<input type="hidden" name="' . hsc($attrs['name']) .'"'
                 . ' value="' . hsc($attrs['value'][1]) . '" />';
        $attrs['value'] = $attrs['value'][0];
    }
    $s .= '<input type="checkbox" '.buildAttributes($attrs,true).' />';
    $s .= ' <span>'.$attrs['_text'].'</span></label>';
    if (preg_match('/(^| )block($| )/', $attrs['_class']))
        $s .= '<br />';
    return $s;
}

/**
 * form_radiofield
 *
 * Print the HTML for a radio button input field.
 *   _class : class attribute used on the label tag
 *   _text  : Text to display after the input. Not escaped.
 * Other attributes are passed to buildAttributes() for the input tag.
 *
 * @author  Tom N Harris <tnharris@whoopdedo.org>
 */
function form_radiofield($attrs) {
    // mandatory attributes
    unset($attrs['type']);
    $s = '<label';
    if ($attrs['_class']) $s .= ' class="'.$attrs['_class'].'"';
    if (!empty($attrs['id'])) $s .= ' for="'.$attrs['id'].'"';
    $s .= '><input type="radio" '.buildAttributes($attrs,true).' />';
    $s .= ' <span>'.$attrs['_text'].'</span></label>';
    if (preg_match('/(^| )block($| )/', $attrs['_class']))
        $s .= '<br />';
    return $s;
}

/**
 * form_menufield
 *
 * Print the HTML for a drop-down menu.
 *   _options : Array of (value,text,selected) for the menu.
 *              Text can be omitted. Text and value are passed to formText()
 *              Only one item can be selected.
 *   _class : class attribute used on the label tag
 *   _text  : Text to display before the menu. Not escaped.
 * Other attributes are passed to buildAttributes() for the input tag.
 *
 * @author  Tom N Harris <tnharris@whoopdedo.org>
 */
function form_menufield($attrs) {
    $attrs['size'] = '1';
    $s = '<label';
    if ($attrs['_class']) $s .= ' class="'.$attrs['_class'].'"';
    if (!empty($attrs['id'])) $s .= ' for="'.$attrs['id'].'"';
    $s .= '><span>'.$attrs['_text'].'</span>';
    $s .= ' <select '.buildAttributes($attrs,true).'>'.DOKU_LF;
    if (!empty($attrs['_options'])) {
        $selected = false;

        $cnt = count($attrs['_options']);
        for($n=0; $n < $cnt; $n++){
            @list($value,$text,$select) = $attrs['_options'][$n];
            $p = '';
            if (!is_null($text))
                $p .= ' value="'.formText($value).'"';
            else
                $text = $value;
            if (!empty($select) && !$selected) {
                $p .= ' selected="selected"';
                $selected = true;
            }
            $s .= '<option'.$p.'>'.formText($text).'</option>';
        }
    } else {
        $s .= '<option></option>';
    }
    $s .= DOKU_LF.'</select></label>';
    if (preg_match('/(^| )block($| )/', $attrs['_class']))
        $s .= '<br />';
    return $s;
}

/**
 * form_listboxfield
 *
 * Print the HTML for a list box.
 *   _options : Array of (value,text,selected) for the list.
 *              Text can be omitted. Text and value are passed to formText()
 *   _class : class attribute used on the label tag
 *   _text  : Text to display before the menu. Not escaped.
 * Other attributes are passed to buildAttributes() for the input tag.
 *
 * @author  Tom N Harris <tnharris@whoopdedo.org>
 */
function form_listboxfield($attrs) {
    $s = '<label';
    if ($attrs['_class']) $s .= ' class="'.$attrs['_class'].'"';
    if (!empty($attrs['id'])) $s .= ' for="'.$attrs['id'].'"';
    $s .= '><span>'.$attrs['_text'].'</span> ';
    $s .= '<select '.buildAttributes($attrs,true).'>'.DOKU_LF;
    if (!empty($attrs['_options'])) {
        foreach ($attrs['_options'] as $opt) {
            @list($value,$text,$select,$disabled) = $opt;
            $p = '';
            if(is_null($text)) $text = $value;
            $p .= ' value="'.formText($value).'"';
            if (!empty($select)) $p .= ' selected="selected"';
            if ($disabled) $p .= ' disabled="disabled"';
            $s .= '<option'.$p.'>'.formText($text).'</option>';
        }
    } else {
        $s .= '<option></option>';
    }
    $s .= DOKU_LF.'</select></label>';
    if (preg_match('/(^| )block($| )/', $attrs['_class']))
        $s .= '<br />';
    return $s;
}
