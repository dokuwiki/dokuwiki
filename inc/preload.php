<?php
/**
* This overwrites the DOKU_CONF. Each animal gets its own configuration and data directory.
*
* The farm ($farm) can be any directory.
* Animals are direct subdirectories of the farm directory. They have to reflect the domain
* name: If an animal resides in http://www.domain.org:8080/mysite/test/ directories that
* will match range from $farm/8080.www.domain.org.mysite.test/ to a simple $farm/domain/.
*/

$farm = 'C:/UniServer/www/dokuwiki-farm';
//$farm = '';

if(!defined('DOKU_CONF')) define('DOKU_CONF', conf_path($farm));
if(!defined('DOKU_FARM')) define('DOKU_FARM', false);

/**
 * Find the appropriate configuration directory.
 *
 * Try finding a matching configuration directory by stripping the website's
 * hostname from left to right and pathname from right to left. The first
 * configuration file found will be used; the remaining will ignored. If no
 * configuration file is found, return the default confdir './conf'.
 *
 * @author Anika Henke <anika@selfthinker.org>
 * @author virtual host part based on conf_path() from Drupal.org's /includes/bootstrap.inc
 *   (see http://cvs.drupal.org/viewvc/drupal/drupal/includes/bootstrap.inc?view=markup)
 */
function conf_path($farm) {

    if (!$farm)
        return DOKU_INC.'conf/';

    // htacces based
    if(isset($_REQUEST['animal'])) {
        if(!is_dir($farm.'/'.$_REQUEST['animal'])) nice_die("Sorry! This Wiki doesn't exist!");
        if(!defined('DOKU_FARM')) define('DOKU_FARM', 'htaccess');
        return $farm.'/'.$_REQUEST['animal'].'/conf/';
    }

    // virtual host based
    $uri = explode('/', $_SERVER['SCRIPT_NAME'] ? $_SERVER['SCRIPT_NAME'] : $_SERVER['SCRIPT_FILENAME']);
    $server = explode('.', implode('.', array_reverse(explode(':', rtrim($_SERVER['HTTP_HOST'], '.')))));
    for ($i = count($uri) - 1; $i > 0; $i--) {
        for ($j = count($server); $j > 0; $j--) {
            $dir = implode('.', array_slice($server, -$j)) . implode('.', array_slice($uri, 0, $i));
            if(is_dir("$farm/$dir/conf/")) {
                if(!defined('DOKU_FARM')) define('DOKU_FARM', 'virtual');
                return "$farm/$dir/conf/";
            }
        }
    }

    // default conf directory in farm
    if(is_dir("$farm/default/conf/")) {
        if(!defined('DOKU_FARM')) define('DOKU_FARM', 'default');
        return "$farm/default/conf/";
    }
    // farmer
    return DOKU_INC.'conf/';
}

//echo conf_path($farm);

/*
$farm = 'W:/www/dokuwiki-farm';
$farmerURL = 'wiki';
if ($_SERVER['SERVER_NAME'] != $farmerURL) {

    // don't do anything if the animal doesn't exist
    if(!is_dir($farm . '/' . $_SERVER['SERVER_NAME'])) nice_die("Sorry! This Wiki doesn't exist!");

    if(!defined('DOKU_CONF')) define('DOKU_CONF', $farm . '/' . $_SERVER['SERVER_NAME'] . '/conf/');
} else {
    if(!defined('DOKU_CONF')) define('DOKU_CONF', DOKU_INC . '/conf/');
}
*/



require_once @DOKU_INC.'inc/config_cascade.php';

$config_cascade = array(
    'main' => array(
        'default'   => array(DOKU_INC.'conf/dokuwiki.php'),
        'local'     => array(DOKU_CONF.'local.php'),
        'protected' => array(DOKU_CONF.'local.protected.php'),
    ),
    'acronyms'  => array(
        'default'   => array(DOKU_INC.'conf/acronyms.conf'),
        'local'     => array(DOKU_CONF.'acronyms.local.conf'),
    ),
    'entities'  => array(
        'default'   => array(DOKU_INC.'conf/entities.conf'),
        'local'     => array(DOKU_CONF.'entities.local.conf'),
    ),
    'interwiki' => array(
        'default'   => array(DOKU_INC.'conf/interwiki.conf'),
        'local'     => array(DOKU_CONF.'interwiki.local.conf'),
    ),
    'license' => array(
        'default'   => array(DOKU_INC.'conf/license.php'),
        'local'     => array(DOKU_CONF.'license.local.php'),
    ),
    'mediameta' => array(
        'default'   => array(DOKU_INC.'conf/mediameta.php'),
        'local'     => array(DOKU_CONF.'mediameta.local.php'),
    ),
    'mime'      => array(
        'default'   => array(DOKU_INC.'conf/mime.conf'),
        'local'     => array(DOKU_CONF.'mime.local.conf'),
    ),
    'scheme'    => array(
        'default'   => array(DOKU_INC.'conf/scheme.conf'),
        'local'     => array(DOKU_CONF.'scheme.local.conf'),
    ),
    'smileys'   => array(
        'default'   => array(DOKU_INC.'conf/smileys.conf'),
        'local'     => array(DOKU_CONF.'smileys.local.conf'),
    ),
    'wordblock' => array(
        'default'   => array(DOKU_INC.'conf/wordblock.conf'),
        'local'     => array(DOKU_CONF.'wordblock.local.conf'),
    ),
    // whatever
    'acl'       => array(
        'default'   => DOKU_CONF.'acl.auth.php',
    ),
    'plainauth.users' => array(
        'default'   => DOKU_CONF.'users.auth.php',
    ),
    'userstyle' => array(
        'default' => DOKU_CONF.'userstyle.css',
        'print'   => DOKU_CONF.'userprint.css',
        'feed'    => DOKU_CONF.'userfeed.css',
        'all'     => DOKU_CONF.'userall.css',
    ),
    'userscript' => array(
        'default' => DOKU_CONF.'userscript.js'
    ),
    'plugins' => array(
        'local'     => array(DOKU_CONF.'plugins.local.php'),
        'protected' => array(
            DOKU_INC.'conf/plugins.required.php',
            DOKU_CONF.'plugins.protected.php',
        ),
    ),
    /*'plugins' => array(
        'default'   => array(DOKU_CONF.'plugins.php'),
        'local'     => array(DOKU_CONF.'plugins.local.php'),
        'protected' => array(DOKU_CONF.'plugins.protected.php'),
    ),*/
);

