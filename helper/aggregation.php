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
     * prepare an array to be passed through buildURLparams()
     *
     * @param string $name keyname
     * @param string|array $array value or key-value pairs
     * @return array
     */
    function _a2ua($name, $array) {
        $urlarray = array();
        foreach((array) $array as $key => $val) {
            $urlarray[$name . '[' . $key . ']'] = $val;
        }
        return $urlarray;
    }

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
            $cur_params = $this->_a2ua('dataflt', $INPUT->arr('dataflt'));
        }
        if($INPUT->has('datasrt')) {
            $cur_params['datasrt'] = $INPUT->str('datasrt');
        }
        if($INPUT->has('dataofs')) {
            $cur_params['dataofs'] = $INPUT->arr('dataofs');
        }

        //combine key and value
        if(!$returnURLparams) {
            $flat_param = array();
            foreach($cur_params as $key => $val) {
                $flat_param[] = $key . $val;
            }
            $cur_params = $flat_param;
        }
        return $cur_params;
    }
}

// vim:ts=4:sw=4:et:
