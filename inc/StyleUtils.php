<?php

namespace dokuwiki;

/**
 * Class StyleUtils
 *
 * Reads and applies the template's style.ini settings
 */
class StyleUtils
{

    /** @var string current template */
    protected $tpl;
    /** @var bool reinitialize styles config */
    protected $reinit;
    /** @var bool $preview preview mode */
    protected $preview;
    /** @var array default replacements to be merged with custom style configs */
    protected $defaultReplacements = array(
        '__text__' => "#000",
        '__background__' => "#fff",
        '__text_alt__' => "#999",
        '__background_alt__' => "#eee",
        '__text_neu__' => "#666",
        '__background_neu__' => "#ddd",
        '__border__' => "#ccc",
        '__highlight__' => "#ff9",
        '__link__' => "#00f",
    );

    /**
     * StyleUtils constructor.
     * @param string $tpl template name: if not passed as argument, the default value from $conf will be used
     * @param bool $preview
     * @param bool $reinit whether static style conf should be reinitialized
     */
    public function __construct($tpl = '', $preview = false, $reinit = false)
    {
        if (!$tpl) {
            global $conf;
            $tpl = $conf['template'];
        }
        $this->tpl = $tpl;
        $this->reinit = $reinit;
        $this->preview = $preview;
    }

    /**
     * Load style ini contents
     *
     * Loads and merges style.ini files from template and config and prepares
     * the stylesheet modes
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     * @author Anna Dabrowska <info@cosmocode.de>
     *
     * @return array with keys 'stylesheets' and 'replacements'
     */
    public function cssStyleini()
    {
        static $combined = [];
        if (!empty($combined) && !$this->reinit) {
            return $combined;
        }

        global $conf;
        global $config_cascade;
        $stylesheets = array(); // mode, file => base

        // guaranteed placeholder => value
        $replacements = $this->defaultReplacements;

        // merge all styles from config cascade
        if (!is_array($config_cascade['styleini'])) {
            trigger_error('Missing config cascade for styleini', E_USER_WARNING);
        }

        // allow replacement overwrites in preview mode
        if ($this->preview) {
            $config_cascade['styleini']['local'][] = $conf['cachedir'] . '/preview.ini';
        }

        $combined['stylesheets'] = [];
        $combined['replacements'] = [];

        foreach (array('default', 'local', 'protected') as $config_group) {
            if (empty($config_cascade['styleini'][$config_group])) continue;

            // set proper server dirs
            $webbase = $this->getWebbase($config_group);

            foreach ($config_cascade['styleini'][$config_group] as $inifile) {
                // replace the placeholder with the name of the current template
                $inifile = str_replace('%TEMPLATE%', $this->tpl, $inifile);

                $incbase = dirname($inifile) . '/';

                if (file_exists($inifile)) {
                    $config = parse_ini_file($inifile, true);

                    if (is_array($config['stylesheets'])) {
                        foreach ($config['stylesheets'] as $inifile => $mode) {
                            // validate and include style files
                            $stylesheets = array_merge(
                                $stylesheets,
                                $this->getValidatedStyles($stylesheets, $inifile, $mode, $incbase, $webbase)
                            );
                            $combined['stylesheets'] = array_merge($combined['stylesheets'], $stylesheets);
                        }
                    }

                    if (is_array($config['replacements'])) {
                        $replacements = array_replace(
                            $replacements,
                            $this->cssFixreplacementurls($config['replacements'], $webbase)
                        );
                        $combined['replacements'] = array_merge($combined['replacements'], $replacements);
                    }
                }
            }
        }

        return $combined;
    }

    /**
     * Checks if configured style files exist and, if necessary, adjusts file extensions in config
     *
     * @param array $stylesheets
     * @param string $file
     * @param string $mode
     * @param string $incbase
     * @param string $webbase
     * @return mixed
     */
    protected function getValidatedStyles($stylesheets, $file, $mode, $incbase, $webbase)
    {
        global $conf;
        if (!file_exists($incbase . $file)) {
            list($extension, $basename) = array_map('strrev', explode('.', strrev($file), 2));
            $newExtension = $extension === 'css' ? 'less' : 'css';
            if (file_exists($incbase . $basename . '.' . $newExtension)) {
                $stylesheets[$mode][$incbase . $basename . '.' . $newExtension] = $webbase;
                if ($conf['allowdebug']) {
                    msg("Stylesheet $file not found, using $basename.$newExtension instead. " .
                        "Please contact developer of \"$this->tpl\" template.", 2);
                }
            } elseif ($conf['allowdebug']) {
                msg("Stylesheet $file not found, please contact the developer of \"$this->tpl\" template.", 2);
            }
        }
        $stylesheets[$mode][fullpath($incbase . $file)] = $webbase;
        return $stylesheets;
    }

    /**
     * Returns the web base path for the given level/group in config cascade.
     * Style resources are relative to the template directory for the main (default) styles
     * but relative to DOKU_BASE for everything else"
     *
     * @param string $config_group
     * @return string
     */
    protected function getWebbase($config_group)
    {
        if ($config_group === 'default') {
            return tpl_basedir($this->tpl);
        } else {
            return DOKU_BASE;
        }
    }

    /**
     * Amend paths used in replacement relative urls, refer FS#2879
     *
     * @author Chris Smith <chris@jalakai.co.uk>
     *
     * @param array $replacements with key-value pairs
     * @param string $location
     * @return array
     */
    protected function cssFixreplacementurls($replacements, $location)
    {
        foreach ($replacements as $key => $value) {
            $replacements[$key] = preg_replace(
                '#(url\([ \'"]*)(?!/|data:|http://|https://| |\'|")#',
                '\\1' . $location,
                $value
            );
        }
        return $replacements;
    }
}
