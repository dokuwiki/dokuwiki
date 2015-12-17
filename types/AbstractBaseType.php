<?php
namespace plugin\struct\types;

use dokuwiki\Form\Form;

/**
 * Class AbstractBaseType
 *
 * This class represents a basic type that can be configured to be used in a Schema. It is the main
 * part of a column definition as defined in meta\Column
 *
 * @package plugin\struct\types
 */
abstract class AbstractBaseType {

    /**
     * @var array current config
     */
    protected $config = array();

    /**
     * @var string label for the field
     */
    protected $label = '';

    /**
     * @var bool is this a multivalue field?
     */
    protected $ismulti = false;

    /**
     * AbstractBaseType constructor.
     * @param array|null $config The configuration, might be null if nothing saved, yet
     * @param string $label The label for this field (empty for new definitions=
     * @param bool $ismulti Should this field accept multiple values?
     */
    public function __construct($config = null, $label = '', $ismulti = false) {
        if(!is_null($config)) $this->config = array_merge($this->config, $config);
        $this->label = $label;
        $this->ismulti = (bool) $ismulti;
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
        return substr(get_class($this), 20);
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
     * Adds the admin schema editor to the given form
     *
     * @param Form $form
     * @return void
     */
    abstract public function schemaEditor(Form $form);

    /**
     * Adds the frontend editor to the given form
     *
     * @param Form $form
     * @return void
     */
    abstract public function frontendEditor(Form $form);

    /**
     * Output the stored data
     *
     * @param string|int $value the value stored in the database
     * @return string the HTML to represent this data
     */
    abstract public function getDisplayData($value);
}
