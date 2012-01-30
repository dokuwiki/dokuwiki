<?php
/**
 * Template Functions
 *
 * This file provides template specific custom functions that are
 * not provided by the DokuWiki core.
 * It is common practice to start each function with an underscore
 * to make sure it won't interfere with future core functions.
 */

// must be run from within DokuWiki
if (!defined('DOKU_INC')) die();

/* @todo: add this function to the core and delete this file */

/**
 * Include additional html file from conf directory if it exists, otherwise use
 * file in the template's root directory.
 *
 * @author Anika Henke <anika@selfthinker.org>
 */
function _tpl_include($fn) {
    $confFile = DOKU_CONF.$fn;
    $tplFile  = dirname(__FILE__).'/'.$fn;

    if (file_exists($confFile))
        include($confFile);
    else if (file_exists($tplFile))
        include($tplFile);
}
