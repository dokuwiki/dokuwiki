<?php
/**
 * DokuWiki Plugin struct (Helper Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Andreas Gohr, Michael Große <dokuwiki@cosmocode.de>
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

class helper_plugin_struct_aggregation extends DokuWiki_Plugin {

    /**
     * get current URL parameters
     *
     * @param bool $returnURLparams
     * @return array with dataflt, datasrt and dataofs parameters
     */
    function _get_current_param($returnURLparams = true) {
        global $INPUT;
        $cur_params = array();
        if($INPUT->has('dataflt')) {
            $cur_params['dataflt'] = $INPUT->arr('dataflt');
        }
        if($INPUT->has('datasrt')) {
            $cur_params['datasrt'] = $INPUT->str('datasrt');
        }
        if($INPUT->has('dataofs')) {
            $cur_params['dataofs'] = $INPUT->int('dataofs');
        }

        return $cur_params;
    }
}

// vim:ts=4:sw=4:et:
