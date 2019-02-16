<?php

namespace dokuwiki;

class StyleUtils
{




    public function cssStyleiniOld($tpl, $preview=false) {
        global $conf;

        $stylesheets = array(); // mode, file => base
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

        // load template's style.ini
        $incbase = tpl_incdir($tpl);
        $webbase = tpl_basedir($tpl);
        $ini = $incbase.'style.ini';
        if(file_exists($ini)){
            $data = parse_ini_file($ini, true);

            // stylesheets
            if(is_array($data['stylesheets'])) foreach($data['stylesheets'] as $file => $mode){
                if (!file_exists($incbase . $file)) {
                    list($extension, $basename) = array_map('strrev', explode('.', strrev($file), 2));
                    $newExtension = $extension === 'css' ? 'less' : 'css';
                    if (file_exists($incbase . $basename . '.' . $newExtension)) {
                        $stylesheets[$mode][$incbase . $basename . '.' . $newExtension] = $webbase;
                        if ($conf['allowdebug']) {
                            msg("Stylesheet $file not found, using $basename.$newExtension instead. Please contact developer of \"{$conf['template']}\" template.", 2);
                        }
                        continue;
                    }
                }
                $stylesheets[$mode][$incbase . $file] = $webbase;
            }

            // replacements
            if(is_array($data['replacements'])){
                $replacements = array_merge($replacements, $this->cssFixreplacementurls($data['replacements'],$webbase));
            }
        }

        // load configs's style.ini
        $webbase = DOKU_BASE;
        $ini = DOKU_CONF."tpl/$tpl/style.ini";
        $incbase = dirname($ini).'/';
        if(file_exists($ini)){
            $data = parse_ini_file($ini, true);

            // stylesheets
            if(isset($data['stylesheets']) && is_array($data['stylesheets'])) foreach($data['stylesheets'] as $file => $mode){
                $stylesheets[$mode][$incbase.$file] = $webbase;
            }

            // replacements
            if(isset($data['replacements']) && is_array($data['replacements'])){
                $replacements = array_merge($replacements, $this->cssFixreplacementurls($data['replacements'],$webbase));
            }
        }

        // allow replacement overwrites in preview mode
        if($preview) {
            $webbase = DOKU_BASE;
            $ini     = $conf['cachedir'].'/preview.ini';
            if(file_exists($ini)) {
                $data = parse_ini_file($ini, true);
                // replacements
                if(is_array($data['replacements'])) {
                    $replacements = array_merge($replacements, $this->cssFixreplacementurls($data['replacements'], $webbase));
                }
            }
        }

        return array(
            'stylesheets' => $stylesheets,
            'replacements' => $replacements
        );
    }



    /**
     * Load style ini contents
     *
     * Loads and merges style.ini files from template and config and prepares
     * the stylesheet modes
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     *
     * @param string $tpl the used template
     * @param bool   $preview load preview replacements
     * @return array with keys 'stylesheets' and 'replacements'
     */
    public function cssStyleini($tpl, $preview=false) {
        global $conf;

        $mergedinis = $this->getStyleIni();

        // allow replacement overwrites in preview mode
        if($preview) {
            $webbase = DOKU_BASE;
            $ini     = $conf['cachedir'].'/preview.ini';
            if(file_exists($ini)) {
                $data = parse_ini_file($ini, true);
                // FIXME does preview mode affect replacements only, or stylesheets too?
                // replacements
                if(is_array($data['replacements'])) {
                    $mergedinis['replacements'] = array_merge(
                        $mergedinis['replacements'],
                        $this->cssFixreplacementurls($data['replacements'], $webbase)
                    );
                }
            }
        }

        return array(
            'stylesheets' => $mergedinis['stylesheets'],
            'replacements' => $mergedinis['replacements']
        );
    }

    protected function getStyleIni() {
        static $inis = null;
        if ( !$inis ) {
            $inis = $this->retrieveStyles('styleini','parse_ini_file', array(true));

            // FIXME is this trim filter still necessary?
            foreach ($inis as $key => &$section) {
                if ($section !== 'replacements') {
                    continue;
                }
                $section = array_map('trim', $section);
                $section = preg_replace('/^#.*/', '', $section);
                $section = array_filter($section);
            }
        }
        return $inis;
    }

    protected function retrieveStyles($type, $fn, $params = array()) {
        global $conf;
        global $config_cascade;
        $incbase = tpl_incdir();
        $stylesheets = array(); // mode, file => base

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

        $combined = [];

        if (!is_array($config_cascade[$type])) {
            trigger_error('Missing config cascade for "'.$type.'"',E_USER_WARNING);
        }

        foreach (array('default','local','protected') as $config_group) {

            if (empty($config_cascade[$type][$config_group])) continue;

            foreach ($config_cascade[$type][$config_group] as $file) {
                // replace the placeholder with the name of the current template
                $file = str_replace('TPL_PLACEHOLDER', $conf['template'], $file);

                if (file_exists($file)) {
                    $config = call_user_func_array($fn, array_merge(array($file), $params));

                    if (is_array($config['stylesheets'])) {
                        foreach($config['stylesheets'] as $file => $mode) {

                            // set proper server dirs
                            $webbase = $this->getServerPath($config_group);

                            // validate and include style files
                            $stylesheets = array_merge($stylesheets, $this->getValidatedStyles($stylesheets, $file, $mode, $incbase, $webbase));

                            // FIXME do we also need to fix paths as in the replacements section below?
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

        return $combined;
    }


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

    protected function getServerPath($config_group) {
        if ($config_group === 'default') {
            return tpl_basedir();
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
