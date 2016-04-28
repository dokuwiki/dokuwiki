<?php
namespace dokuwiki\plugin\struct\types;

use dokuwiki\plugin\struct\meta\Column;
use dokuwiki\plugin\struct\meta\StructException;
use dokuwiki\plugin\struct\meta\ValidationException;

/**
 * Class AbstractBaseType
 *
 * This class represents a basic type that can be configured to be used in a Schema. It is the main
 * part of a column definition as defined in meta\Column
 *
 * This defines also how the content of the coulmn will be entered and formatted.
 *
 * @package dokuwiki\plugin\struct\types
 * @see Column
 */
abstract class AbstractBaseType {

    /**
     * @var array current config
     */
    protected $config = array();

    /**
     * @var array config keys that should not be cleaned despite not being in $config
     */
    protected $keepconfig = array('label', 'hint', 'visibility');

    /**
     * @var string label for the field
     */
    protected $label = '';

    /**
     * @var bool is this a multivalue field?
     */
    protected $ismulti = false;

    /**
     * @var int the type ID
     */
    protected $tid = 0;

    /**
     * @var null|Column the column context this type is part of
     */
    protected $context = null;

    /**
     * @var \DokuWiki_Plugin
     */
    protected $hlp = null;

    /**
     * AbstractBaseType constructor.
     * @param array|null $config The configuration, might be null if nothing saved, yet
     * @param string $label The label for this field (empty for new definitions=
     * @param bool $ismulti Should this field accept multiple values?
     * @param int $tid The id of this type if it has been saved, yet
     */
    public function __construct($config = null, $label = '', $ismulti = false, $tid = 0) {
        // general config options
        $baseconfig = array(
            'visibility' => array(
                'inpage' => true,
                'ineditor' => true,
            )
        );

        // use previously saved configuration, ignoring all keys that are not supposed to be here
        if(!is_null($config)) {
            foreach($config as $key => $value) {
                if(isset($this->config[$key]) || in_array($key, $this->keepconfig)) {
                    $this->config[$key] = $value;
                }
            }
        }

        $this->initTransConfig();
        $this->config = array_merge($baseconfig, $this->config);
        $this->label = $label;
        $this->ismulti = (bool) $ismulti;
        $this->tid = $tid;
    }

    /**
     * Add the translatable keys to the configuration
     *
     * This checks if a configuration for the translation plugin exists and if so
     * adds all configured languages to the config array. This ensures all types
     * can have translatable labels.
     */
    protected function initTransConfig() {
        global $conf;
        $lang = $conf['lang'];
        if(isset($conf['plugin']['translation']['translations'])) {
            $lang .= ' ' . $conf['plugin']['translation']['translations'];
        }
        $langs = explode(' ', $lang);
        $langs = array_map('trim', $langs);
        $langs = array_filter($langs);
        $langs = array_unique($langs);

        if(!isset($this->config['label'])) $this->config['label'] = array();
        if(!isset($this->config['hint'])) $this->config['hint'] = array();
        // initialize missing keys
        foreach($langs as $lang) {
            if(!isset($this->config['label'][$lang])) $this->config['label'][$lang] = '';
            if(!isset($this->config['hint'][$lang])) $this->config['hint'][$lang] = '';
        }
        // strip unknown languages
        foreach(array_keys($this->config['label']) as $key) {
            if(!in_array($key, $langs)) unset($this->config['label'][$key]);
        }
        foreach(array_keys($this->config['hint']) as $key) {
            if(!in_array($key, $langs)) unset($this->config['hint'][$key]);
        }

    }

    /**
     * Returns data as associative array
     *
     * @return array
     */
    public function getAsEntry() {
        return array(
            'config' => json_encode($this->config),
            'label' => $this->label,
            'ismulti' => $this->ismulti,
            'class' => $this->getClass()
        );
    }

    /**
     * The class name of this type (no namespace)
     * @return string
     */
    public function getClass() {
        $class = get_class($this);
        return substr($class, strrpos($class, "\\") + 1);
    }

    /**
     * Return the current configuration for this type
     *
     * @return array
     */
    public function getConfig() {
        return $this->config;
    }

    /**
     * @return boolean
     */
    public function isMulti() {
        return $this->ismulti;
    }

    /**
     * @return string
     */
    public function getLabel() {
        return $this->label;
    }

    /**
     * Returns the translated label for this type
     *
     * Uses the current language as determined by $conf['lang']. Falls back to english
     * and then to the Schema label
     *
     * @return string
     */
    public function getTranslatedLabel() {
        global $conf;
        $lang = $conf['lang'];
        if(!blank($this->config['label'][$lang])) {
            return $this->config['label'][$lang];
        }
        if(!blank($this->config['label']['en'])) {
            return $this->config['label']['en'];
        }
        return $this->label;
    }

    /**
     * Returns the translated hint for this type
     *
     * Uses the current language as determined by $conf['lang']. Falls back to english.
     * Returns empty string if no hint is configured
     *
     * @return string
     */
    public function getTranslatedHint() {
        global $conf;
        $lang = $conf['lang'];
        if(!blank($this->config['hint'][$lang])) {
            return $this->config['hint'][$lang];
        }
        if(!blank($this->config['hint']['en'])) {
            return $this->config['hint']['en'];
        }
        return '';
    }

    /**
     * @return int
     */
    public function getTid() {
        return $this->tid;
    }

    /**
     * @throws StructException
     * @return Column
     */
    public function getContext() {
        if(is_null($this->context))
            throw new StructException('Empty column context requested. Type was probably initialized outside of Schema.');
        return $this->context;
    }

    /**
     * @param Column $context
     */
    public function setContext($context) {
        $this->context = $context;
    }

    /**
     * @return bool
     */
    public function isVisibleInEditor() {
        return $this->config['visibility']['ineditor'];
    }

    /**
     * @return bool
     */
    public function isVisibleInPage() {
        return $this->config['visibility']['inpage'];
    }

    /**
     * Split a single value into multiple values
     *
     * This function is called on saving data when only a single value instead of an array
     * was submitted.
     *
     * Types implementing their own @see multiValueEditor() will probably want to override this
     *
     * @param string $value
     * @return array
     */
    public function splitValues($value) {
        return array_map('trim', explode(',', $value));
    }

    /**
     * Return the editor to edit multiple values
     *
     * Types can override this to provide a better alternative than multiple entry fields
     *
     * @param string $name the form base name where this has to be stored
     * @param string[] $values the current values
     * @return string html
     */
    public function multiValueEditor($name, $values) {
        $html = '';
        foreach($values as $value) {
            $html .= '<div class="multiwrap">';
            $html .= $this->valueEditor($name . '[]', $value);
            $html .= '</div>';
        }
        // empty field to add
        $html .= '<div class="newtemplate">';
        $html .= '<div class="multiwrap">';
        $html .= $this->valueEditor($name . '[]', '');
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Return the editor to edit a single value
     *
     * @param string $name the form name where this has to be stored
     * @param string $value the current value
     * @return string html
     */
    public function valueEditor($name, $value) {
        $class = 'struct_' . strtolower($this->getClass());

        // support the autocomplete configurations out of the box
        if(isset($this->config['autocomplete']['maxresult']) && $this->config['autocomplete']['maxresult']) {
            $class .= ' struct_autocomplete';
        }

        $name = hsc($name);
        $value = hsc($value);
        $html = "<input name=\"$name\" value=\"$value\" class=\"$class\" />";
        return "$html";
    }

    /**
     * Output the stored data
     *
     * @param string|int $value the value stored in the database
     * @param \Doku_Renderer $R the renderer currently used to render the data
     * @param string $mode The mode the output is rendered in (eg. XHTML)
     * @return bool true if $mode could be satisfied
     */
    public function renderValue($value, \Doku_Renderer $R, $mode) {
        $R->cdata($value);
        return true;
    }

    /**
     * format and return the data
     *
     * @param int[]|string[] $values the values stored in the database
     * @param \Doku_Renderer $R the renderer currently used to render the data
     * @param string $mode The mode the output is rendered in (eg. XHTML)
     * @return bool true if $mode could be satisfied
     */
    public function renderMultiValue($values, \Doku_Renderer $R, $mode) {
        $len = count($values);
        for($i = 0; $i < $len; $i++) {
            $this->renderValue($values[$i], $R, $mode);
            if($i < $len - 1) {
                $R->cdata(', ');
            }
        }
        return true;
    }

    /**
     * This function builds a where clause for this column, comparing
     * the current value stored in $column with $value. Types can use it to do
     * clever things with the comparison.
     *
     * This default implementation is probably good enough for most basic types
     *
     * @param string $column The column name to us in the SQL
     * @param string $comp The comparator @see Search::$COMPARATORS
     * @param string $value
     * @return array Tuple with the SQL and parameter array
     */
    public function compare($column, $comp, $value) {
        switch($comp) {
            case '~':
                $sql = "$column LIKE ?";
                $opt = array($value);
                break;
            case '!~':
                $sql = "$column NOT LIKE ?";
                $opt = array($value);
                break;
            default:
                $sql = "$column $comp ?";
                $opt = array($value);
        }

        return array($sql, $opt);
    }

    /**
     * Validate and optionally clean a single value
     *
     * This function needs to throw a validation exception when validation fails.
     * The exception message will be prefixed by the appropriate field on output
     *
     * The function should return the value as it should be saved later on.
     *
     * @param string|int $value
     * @return int|string the cleaned value
     * @throws ValidationException
     */
    public function validate($value) {
        return trim($value);
    }

    /**
     * Overwrite to handle Ajax requests
     *
     * A call to DOKU_BASE/lib/exe/ajax.php?call=plugin_struct&column=schema.name will
     * be redirected to this function on a fully initialized type. The result is
     * JSON encoded and returned to the caller. Access additional parameter via $INPUT
     * as usual
     *
     * @throws StructException when something goes wrong
     * @return mixed
     */
    public function handleAjax() {
        throw new StructException('not implemented');
    }

    /**
     * Convenience method to access plugin language strings
     *
     * @param string $string
     * @return string
     */
    public function getLang($string) {
        if(is_null($this->hlp)) $this->hlp = plugin_load('helper', 'struct');
        return $this->hlp->getLang($string);
    }
}
