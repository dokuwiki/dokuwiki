<?php

namespace dokuwiki\plugin\config\core;

use dokuwiki\plugin\config\core\Setting\Setting;
use dokuwiki\plugin\config\core\Setting\SettingNoClass;
use dokuwiki\plugin\config\core\Setting\SettingNoDefault;
use dokuwiki\plugin\config\core\Setting\SettingNoKnownClass;
use dokuwiki\plugin\config\core\Setting\SettingUndefined;

/**
 * Holds all the current settings and proxies the Loader and Writer
 *
 * @author Chris Smith <chris@jalakai.co.uk>
 * @author Ben Coburn <btcoburn@silicodon.net>
 * @author Andreas Gohr <andi@splitbrain.org>
 */
class Configuration {

    const KEYMARKER = '____';

    /** @var Setting[] metadata as array of Settings objects */
    protected $settings = array();
    /** @var Setting[] undefined and problematic settings */
    protected $undefined = array();

    /** @var array all metadata */
    protected $metadata;
    /** @var array all default settings */
    protected $default;
    /** @var array all local settings */
    protected $local;
    /** @var array all protected settings */
    protected $protected;

    /** @var bool have the settings been changed since loading from disk? */
    protected $changed = false;

    /** @var Loader */
    protected $loader;
    /** @var Writer */
    protected $writer;

    /**
     * ConfigSettings constructor.
     */
    public function __construct() {
        $this->loader = new Loader(new ConfigParser());
        $this->writer = new Writer();

        $this->metadata = $this->loader->loadMeta();
        $this->default = $this->loader->loadDefaults();
        $this->local = $this->loader->loadLocal();
        $this->protected = $this->loader->loadProtected();

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
     * Get all unknown or problematic settings
     *
     * @return Setting[]
     */
    public function getUndefined() {
        return $this->undefined;
    }

    /**
     * Have the settings been changed since loading from disk?
     *
     * @return bool
     */
    public function hasChanged() {
        return $this->changed;
    }

    /**
     * Check if the config can be written
     *
     * @return bool
     */
    public function isLocked() {
        return $this->writer->isLocked();
    }

    /**
     * Update the settings using the data provided
     *
     * @param array $input as posted
     * @return bool true if all updates went through, false on errors
     */
    public function updateSettings($input) {
        $ok = true;

        foreach($this->settings as $key => $obj) {
            $value = isset($input[$key]) ? $input[$key] : null;
            if($obj->update($value)) {
                $this->changed = true;
            }
            if($obj->hasError()) $ok = false;
        }

        return $ok;
    }

    /**
     * Save the settings
     *
     * This save the current state as defined in this object, including the
     * undefined settings
     *
     * @throws \Exception
     */
    public function save() {
        // only save the undefined settings that have not been handled in settings
        $undefined = array_diff_key($this->undefined, $this->settings);
        $this->writer->save(array_merge($this->settings, $undefined));
    }

    /**
     * Touch the settings
     *
     * @throws \Exception
     */
    public function touch() {
        $this->writer->touch();
    }

    /**
     * Load the extension language strings
     *
     * @return array
     */
    public function getLangs() {
        return $this->loader->loadLangs();
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
                $this->undefined[$key] = new SettingNoDefault($key);
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
            $this->settings[$key] = $obj;
        } else {
            $obj = new SettingUndefined($key);
            $this->undefined[$key] = $obj;
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
        if(is_string($class)) {
            $modern = str_replace('_', '', ucwords($class, '_'));
            $modern = '\\dokuwiki\\plugin\\config\\core\\Setting\\Setting' . $modern;
            if($modern && class_exists($modern)) return $modern;
            // try class as given
            if(class_exists($class)) return $class;
            // class wasn't found add to errors
            $this->undefined[$key] = new SettingNoKnownClass($key);
        } else {
            // no class given, add to errors
            $this->undefined[$key] = new SettingNoClass($key);
        }
        return '\\dokuwiki\\plugin\\config\\core\\Setting\\Setting';
    }

}
