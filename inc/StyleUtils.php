<?php

namespace dokuwiki;

class StyleUtils
{

    /** @var string current template */
    protected $tpl;
    /** @var bool reinitialize styles config */
    protected $reinit;
    /** @var bool $preview preview mode */
    protected $preview;

    /**
     * StyleUtils constructor.
     * @link https://codesearch.dokuwiki.org/search?project=dokuwiki&project=plugin&project=template&q=cssStyleini&defs=&refs=&path=&hist=&type=
     * @param string $tpl template name
     * @param bool $preview
     * @param bool $reinit whether static style conf should be reinitialized
     */
    public function __construct($tpl = '', $preview = false, $reinit = false)
    {
        if (!$tpl) {
            global $conf;
            $tpl = $conf['conf'];
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
    public function cssStyleini() {
        global $conf;

        $mergedinis = $this->mergeStyleInis();

        return array(
            'stylesheets' => $mergedinis['stylesheets'],
            'replacements' => $mergedinis['replacements']
        );
    }

    /**
     * @return array
     */
    protected function mergeStyleInis() {
        static $combined = [];
        if (empty($combined) || $this->reinit) {
            global $conf;
            global $config_cascade;
            $stylesheets = array(); // mode, file => base
            $incbase = tpl_incdir($this->tpl);

            // guaranteed placeholder => value
            $replacements = array(
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

            // merge all styles from config cascade
            if (!is_array($config_cascade['styleini'])) {
                trigger_error('Missing config cascade for styleini',E_USER_WARNING);
            }

            // allow replacement overwrites in preview mode
            if($this->preview) {
                $config_cascade['styleini']['local'][] = $conf['cachedir'].'/preview.ini';
            }

            foreach (array('default','local','protected') as $config_group) {
                if (empty($config_cascade['styleini'][$config_group])) continue;

                // set proper server dirs
                $webbase = $this->getServerPath($config_group);

                foreach ($config_cascade['styleini'][$config_group] as $file) {
                    // replace the placeholder with the name of the current template
                    $file = str_replace('%TEMPLATE%', $this->tpl, $file);

                    if (file_exists($file)) {
                        $config = call_user_func_array('parse_ini_file', array_merge(array($file), array(true)));

                        if (is_array($config['stylesheets'])) {
                            foreach($config['stylesheets'] as $file => $mode) {

                                // validate and include style files
                                $stylesheets = array_merge($stylesheets, $this->getValidatedStyles($stylesheets, $file, $mode, $incbase, $webbase));
                                $combined['stylesheets'] = is_array($combined['stylesheets']) ? array_merge($combined['stylesheets'], $stylesheets) : $stylesheets;
                            }
                        }

                        if (is_array($config['replacements'])) {
                            $replacements = array_replace($replacements, $this->cssFixreplacementurls($config['replacements'], $webbase));
                            $combined['replacements'] = array_merge($replacements, $config['replacements']);
                        }
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
    protected function getValidatedStyles($stylesheets, $file, $mode, $incbase, $webbase) {
        global $conf;
        if (!file_exists($incbase . $file)) {
            list($extension, $basename) = array_map('strrev', explode('.', strrev($file), 2));
            $newExtension = $extension === 'css' ? 'less' : 'css';
            if (file_exists($incbase . $basename . '.' . $newExtension)) {
                $stylesheets[$mode][$incbase . $basename . '.' . $newExtension] = $webbase;
                if ($conf['allowdebug']) {
                    msg("Stylesheet $file not found, using $basename.$newExtension instead. Please contact developer of \"{$conf['template']}\" template.", 2);
                }
            }
        }
        $stylesheets[$mode][$incbase . $file] = $webbase;
        return $stylesheets;
    }

    /**
     * Returns the server path for the given level/group in config cascade
     *
     * @param string $config_group
     * @return string
     */
    protected function getServerPath($config_group) {
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
    protected function cssFixreplacementurls($replacements, $location) {
        foreach($replacements as $key => $value) {
            $replacements[$key] = preg_replace('#(url\([ \'"]*)(?!/|data:|http://|https://| |\'|")#','\\1'.$location,$value);
        }
        return $replacements;
    }
}
