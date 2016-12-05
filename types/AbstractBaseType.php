<?php
namespace dokuwiki\plugin\struct\types;

use dokuwiki\plugin\struct\meta\Column;
use dokuwiki\plugin\struct\meta\QueryBuilder;
use dokuwiki\plugin\struct\meta\QueryBuilderWhere;
use dokuwiki\plugin\struct\meta\StructException;
use dokuwiki\plugin\struct\meta\ValidationException;
use dokuwiki\plugin\struct\meta\Value;

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
            $this->mergeConfig($config, $this->config);
        }

        $this->initTransConfig();
        $this->config = array_merge($baseconfig, $this->config);
        $this->label = $label;
        $this->ismulti = (bool) $ismulti;
        $this->tid = $tid;
    }

    /**
     * Merge the current config with the base config of the type
     *
     * Ignores all keys that are not supposed to be there. Recurses into sub keys
     *
     * @param array $current Current configuration
     * @param array $config Base Type configuration
     */
    protected function mergeConfig($current, &$config) {
        foreach($current as $key => $value) {
            if(isset($config[$key]) || in_array($key, $this->keepconfig)) {
                if(is_array($config[$key])) {
                    $this->mergeConfig($value, $config[$key]);
                } else {
                    $config[$key] = $value;
                }
            }
        }
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
     * @param string[] $rawvalues the current values
     * @return string html
     */
    public function multiValueEditor($name, $rawvalues) {
        $html = '';
        foreach($rawvalues as $value) {
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
     * @param string $name  the form name where this has to be stored
     * @param string $rawvalue the current value
     * @return string html
     */
    public function valueEditor($name, $rawvalue) {
        $class = 'struct_' . strtolower($this->getClass());

        // support the autocomplete configurations out of the box
        if(isset($this->config['autocomplete']['maxresult']) && $this->config['autocomplete']['maxresult']) {
            $class .= ' struct_autocomplete';
        }

        $name = hsc($name);
        $rawvalue = hsc($rawvalue);
        $html = "<input name=\"$name\" value=\"$rawvalue\" class=\"$class\" />";
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
        $value = $this->displayValue($value);
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
     * This function is used to modify an aggregation query to add a filter
     * for the given column matching the given value. A type should add at
     * least a filter here but could do additional things like joining more
     * tables needed to handle more complex filters
     *
     * Important: $value might be an array. If so, the filter should check against
     * all provided values ORed together
     *
     * @param QueryBuilder $QB the query so far
     * @param string $tablealias The table the currently saved value(s) are stored in
     * @param string $colname The column name on above table to use in the SQL
     * @param string $comp The SQL comparator (LIKE, NOT LIKE, =, !=, etc)
     * @param string|string[] $value this is the user supplied value to compare against. might be multiple
     * @param string $op the logical operator this filter should use (AND|OR)
     */
    public function filter(QueryBuilder $QB, $tablealias, $colname, $comp, $value, $op) {
        /** @var QueryBuilderWhere $add Where additionional queries are added to*/
        if(is_array($value)) {
            $add = $QB->filters()->where($op); // sub where group
            $op = 'OR';
        } else {
            $add = $QB->filters(); // main where clause
        }
        foreach((array) $value as $item) {
            $pl = $QB->addValue($item);
            $add->where($op, "$tablealias.$colname $comp $pl");
        }
    }

    /**
     * Add the proper selection for this type to the current Query
     *
     * The default implementation here should be good for nearly all types, it simply
     * passes the given parameters to the query builder. But type may do more fancy
     * stuff here, eg. join more tables or select multiple values and combine them to
     * JSON. If you do, be sure implement a fitting rawValue() method.
     *
     * The passed $tablealias.$columnname might be a data_* table (referencing a single
     * row) or a multi_* table (referencing multiple rows). In the latter case the
     * multi table has already been joined with the proper conditions.
     *
     * You may assume a column alias named 'PID' to be available, should you need the
     * current page context for a join or sub select.
     *
     * @param QueryBuilder $QB
     * @param string $tablealias The table the currently saved value(s) are stored in
     * @param string $colname The column name on above table
     * @param string $alias The added selection *has* to use this column alias
     */
    public function select(QueryBuilder $QB, $tablealias, $colname, $alias) {
        $QB->addSelectColumn($tablealias, $colname, $alias);
    }

    /**
     * Sort results by this type
     *
     * The default implementation should be good for nearly all types. However some
     * types may need to do proper SQLite type casting to have the right order.
     *
     * Generally if you implemented @see select() you probably want to implement this,
     * too.
     *
     * @param QueryBuilder $QB
     * @param string $tablealias The table the currently saved value is stored in
     * @param string $colname The column name on above table (always single column!)
     * @param string $order either ASC or DESC
     */
    public function sort(QueryBuilder $QB, $tablealias, $colname, $order) {
        $QB->addOrderBy("$tablealias.$colname $order");
    }

    /**
     * Get the string by which to sort values of this type
     *
     * This implementation is designed to work both as registered function in sqlite
     * and to provide a string to be used in sorting values of this type in PHP.
     *
     * @param string|Value $string The string by which the types would usually be sorted
     */
    public function getSortString($value) {
        if (is_string($value)) {
            return $value;
        }
        $display = $value->getDisplayValue();
        if (is_array($display)) {
            return blank($display[0]) ? "" : $display[0];
        }
        return $display;
    }

    /**
     * This allows types to apply a transformation to the value read by select()
     *
     * The returned value should always be a single, non-complex string. In general
     * it is the identifier a type stores in the database.
     *
     * This value will be used wherever the raw saved data is needed for comparisons.
     * The default implementations of renderValue() and valueEditor() will call this
     * function as well.
     *
     * @param string $value The value as returned by select()
     * @return string The value as saved in the database
     */
    public function rawValue($value) {
        return $value;
    }

    /**
     * This is called when a single string is needed to represent this Type's current
     * value as a single (non-HTML) string. Eg. in a dropdown or in autocompletion.
     *
     * @param string $value
     * @return string
     */
    public function displayValue($value) {
        return $this->rawValue($value);
    }

    /**
     * Validate and optionally clean a single value
     *
     * This function needs to throw a validation exception when validation fails.
     * The exception message will be prefixed by the appropriate field on output
     *
     * The function should return the value as it should be saved later on.
     *
     * @param string|int $rawvalue
     * @return int|string the cleaned value
     * @throws ValidationException
     */
    public function validate($rawvalue) {
        return trim($rawvalue);
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
