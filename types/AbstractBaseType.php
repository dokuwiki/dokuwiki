<?php
namespace plugin\struct\types;

use dokuwiki\Form\Form;

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

    /** @var int sorting of fields */
    protected $sort = 0;

    /**
     * AbstractBaseType constructor.
     * @param int $sort This value is not stored with the type but adding it here, helps keeping things simple
     * @param array|null $config The configuration, might be null if nothing saved, yet
     * @param string $label The label for this field (empty for new definitions=
     * @param bool $ismulti Should this field accept multiple values?
     */
    public function __construct($sort = 0, $config = null, $label = '', $ismulti = false) {
        if(!is_null($config)) $this->config = array_merge($this->config, $config);
        $this->label = $label;
        $this->ismulti = (bool) $ismulti;
        $this->sort = (int) $sort;
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
     * @return int
     */
    public function getSort() {
        return $this->sort;
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
