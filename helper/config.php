<?php
/**
 * DokuWiki Plugin struct (Helper Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Andreas Gohr, Michael Große <dokuwiki@cosmocode.de>
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

class helper_plugin_struct_config extends DokuWiki_Plugin {

    /**
     * @param string $val
     *
     * @return array
     */
    public function parseSort($val) {
        if(substr($val, 0, 1) == '^') {
            return array(substr($val, 1), false);
        }
        return array($val, true);
    }

    /**
     * @param $logic
     * @param $val
     *
     * @return array|bool
     */
    public function parseFilterLine($logic, $val) {
        $flt = $this->parseFilter($val);
        if($flt) {
            $flt[] = $logic;
            return $flt;
        }
        return false;
    }

    /**
     * Parse a filter
     *
     * @param string $val
     *
     * @return array ($col, $comp, $value)
     * @throws dokuwiki\plugin\struct\meta\StructException
     */
    protected function parseFilter($val) {

        $comps = dokuwiki\plugin\struct\meta\Search::$COMPARATORS;
        $comps[] = '*~';
        array_unshift($comps, '<>');
        $comps = array_map('preg_quote_cb', $comps);
        $comps = join('|', $comps);

        if(!preg_match('/^(.*?)('.$comps.')(.*)$/', $val, $match)) {
            throw new dokuwiki\plugin\struct\meta\StructException('Invalid search filter %s', hsc($val));
        }
        array_shift($match); // we don't need the zeroth match
        $match[0] = trim($match[0]);
        $match[2] = trim($match[2]);
        return $match;
    }
}

// vim:ts=4:sw=4:et:
