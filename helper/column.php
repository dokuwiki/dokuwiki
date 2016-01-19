<?php
/**
 * DokuWiki Plugin struct (Helper Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Andreas Gohr, Michael Große <dokuwiki@cosmocode.de>
 */

use plugin\struct\types\AbstractBaseType;

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

class helper_plugin_struct_column extends DokuWiki_Plugin {

    /**
     * Returns a list of all available types
     *
     * @fixme does that make sense in a helper plugin? Should it be a static method of Column?
     * @return array
     */
    static public function getTypes() {
        $types = array();
        $files = glob(DOKU_PLUGIN . 'struct/types/*.php');
        foreach($files as $file) {
            $file = basename($file, '.php');
            if(substr($file, 0, 8) == 'Abstract') continue;
            $types[] = $file;
        }
        sort($types);

        return $types;
    }

}

// vim:ts=4:sw=4:et:
