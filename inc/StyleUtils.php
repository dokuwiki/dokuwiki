<?php

namespace dokuwiki;

class StyleUtils
{
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
