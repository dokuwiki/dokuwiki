<?php

namespace dokuwiki\plugin\config\core;

/**
 * Holds all the current settings
 */
class ConfigSettings {

    /** @var Setting[] metadata as array of Settings objects */
    protected $settings = array();
    /** @var array problematic keys */
    protected $errors;
    /** @var Setting undefined settings */
    protected $undefined = array();

    /** @var array all metadata */
    protected $metadata;
    /** @var array all default settings */
    protected $default;
    /** @var array all local settings */
    protected $local;
    /** @var array all protected settings */
    protected $protected;

    /**
     * ConfigSettings constructor.
     */
    public function __construct() {
        $loader = new Loader(new ConfigParser());

        $this->metadata = $loader->loadMeta();
        $this->default = $loader->loadDefaults();
        $this->local = $loader->loadLocal();
        $this->protected = $loader->loadProtected();

        $this->initSettings();
    }

    /**
     * Get all settings
     *
     * @return Setting[]
     */
    public function getSettings() {
        return $this->settings;
    }

    /**
     * Get all unknown settings
     *
     * @return Setting
     */
    public function getUndefined() {
        return $this->undefined;
    }

    /**
     * Get the settings that had some kind of setup problem
     *
     * @return array associative error, key is the setting, value the error
     */
    public function getErrors() {
        return $this->errors;
    }

    /**
     * Initalizes the $settings and $undefined properties
     */
    protected function initSettings() {
        $keys = array_merge(
            array_keys($this->metadata),
            array_keys($this->default),
            array_keys($this->local),
            array_keys($this->protected)
        );
        $keys = array_unique($keys);

        foreach($keys as $key) {
            $obj = $this->instantiateClass($key);

            if($obj->shouldHaveDefault() && !isset($this->default[$key])) {
                $this->errors[$key] = 'no default';
            }

            $d = isset($this->default[$key]) ? $this->default[$key] : null;
            $l = isset($this->local[$key]) ? $this->local[$key] : null;
            $p = isset($this->protected[$key]) ? $this->protected[$key] : null;

            $obj->initialize($d, $l, $p);
        }
    }

    /**
     * Instantiates the proper class for the given config key
     *
     * The class is added to the $settings or $undefined arrays and returned
     *
     * @param string $key
     * @return Setting
     */
    protected function instantiateClass($key) {
        if(isset($this->metadata[$key])) {
            $param = $this->metadata[$key];
            $class = $this->determineClassName(array_shift($param), $key); // first param is class
            $obj = new $class($key, $param);
            $this->settings[] = $obj;
        } else {
            $obj = new SettingUndefined($key);
            $this->undefined[] = $obj;
        }
        return $obj;
    }

    /**
     * Return the class to load
     *
     * @param string $class the class name as given in the meta file
     * @param string $key the settings key
     * @return string
     */
    protected function determineClassName($class, $key) {
        // try namespaced class first
        if($class) {
            $modern = str_replace('_', '', ucwords($class, '_'));
            $modern = '\\dokuwiki\\plugin\\config\\core\\' . $modern;
            if($modern && class_exists($modern)) return $modern;
            // class wasn't found add to errors
            $this->errors[$key] = 'unknown class';
        } else {
            // no class given, add to errors
            $this->errors[$key] = 'no class';
        }
        return '\\dokuwiki\\plugin\\config\\core\\Setting';
    }

}
