<?php

namespace  dokuwiki\plugin\struct\meta;

trait TranslationUtilities {


    /**
     * Add the translatable keys to the configuration
     *
     * This checks if a configuration for the translation plugin exists and if so
     * adds all configured languages to the config array. This ensures all types
     * can have translatable labels.
     *
     * @param string[] $keysToInitialize the keys for which to initialize language fields
     */
    protected function initTransConfig(array $keysToInitialize = array('label', 'hint')) {
        global $conf;
        $lang = $conf['lang'];
        if(isset($conf['plugin']['translation']['translations'])) {
            $lang .= ' ' . $conf['plugin']['translation']['translations'];
        }
        $langs = explode(' ', $lang);
        $langs = array_map('trim', $langs);
        $langs = array_filter($langs);
        $langs = array_unique($langs);

        foreach ($keysToInitialize as $key) {
            if (!isset($this->config[$key])) {
                $this->config[$key] = array();
            }
            // initialize missing keys
            foreach ($langs as $lang) {
                if (!isset($this->config[$key][$lang])) {
                    $this->config[$key][$lang] = '';
                }
            }
            // strip unknown languages
            foreach (array_keys($this->config[$key]) as $langKey) {
                if (!in_array($langKey, $langs)) {
                    unset($this->config[$key][$langKey]);
                }
            }
        }

    }

    /**
     * Returns the translated key
     *
     * Uses the current language as determined by $conf['lang']. Falls back to english
     * and then to the provided default
     *
     * @param string $key
     * @param string $default the default to return if there is no translation set for $key
     *
     * @return string
     */
    public function getTranslatedKey($key, $default) {
        global $conf;
        $lang = $conf['lang'];
        if(!blank($this->config[$key][$lang])) {
            return $this->config[$key][$lang];
        }
        if(!blank($this->config[$key]['en'])) {
            return $this->config[$key]['en'];
        }
        return $default;
    }
}
