<?php

/**
 * This overwrites DOKU_CONF. Each animal gets its own configuration and data directory.
 * This can be used together with preload.php. See preload.php.dist for an example setup.
 * For more information see http://www.dokuwiki.org/farms.
 *
 * The farm directory (constant DOKU_FARMDIR) can be any directory and needs to be set.
 * Animals are direct subdirectories of the farm directory.
 * There are two different approaches:
 *  * An .htaccess based setup can use any animal directory name:
 *    http://example.org/<path_to_farm>/subdir/ will need the subdirectory '$farm/subdir/'.
 *  * A virtual host based setup needs animal directory names which have to reflect
 *    the domain name: If an animal resides in http://www.example.org:8080/mysite/test/,
 *    directories that will match range from '$farm/8080.www.example.org.mysite.test/'
 *    to a simple '$farm/domain/'.
 *
 * @author Anika Henke <anika@selfthinker.org>
 * @author Michael Klier <chi@chimeric.de>
 * @author Christopher Smith <chris@jalakai.co.uk>
 * @author virtual host part of farm_confpath() based on conf_path() from Drupal.org's /includes/bootstrap.inc
 *   (see https://github.com/drupal/drupal/blob/7.x/includes/bootstrap.inc#L537)
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 */

// DOKU_FARMDIR needs to be set in preload.php, the fallback is the same as DOKU_INC would be (if it was set already)
if (!defined('DOKU_FARMDIR')) define('DOKU_FARMDIR', fullpath(__DIR__ . '/../') . '/');
if (!defined('DOKU_CONF')) define('DOKU_CONF', farm_confpath(DOKU_FARMDIR));
if (!defined('DOKU_FARM')) define('DOKU_FARM', false);

/**
 * Find the appropriate configuration directory.
 *
 * If the .htaccess based setup is used, the configuration directory can be
 * any subdirectory of the farm directory.
 *
 * Otherwise try finding a matching configuration directory by stripping the
 * website's hostname from left to right and pathname from right to left. The
 * first configuration file found will be used; the remaining will ignored.
 * If no configuration file is found, return the default confdir './conf'.
 *
 * @param string $farm
 *
 * @return string
 */
function farm_confpath($farm)
{

    // htaccess based or cli
    // cli usage example: animal=your_animal bin/indexer.php
    if (isset($_GET['animal']) || ('cli' == PHP_SAPI && isset($_SERVER['animal']))) {
        $mode = isset($_GET['animal']) ? 'htaccess' : 'cli';
        $animal = $mode == 'htaccess' ? $_GET['animal'] : $_SERVER['animal'];
        if (isset($_GET['animal'])) {
            // now unset the parameter to not leak into new queries
            // code by @splitbrain from farmer plugin
            unset($_GET['animal']);
            $params = [];
            parse_str($_SERVER['QUERY_STRING'], $params);
            if (isset($params['animal'])) unset($params['animal']);
            $_SERVER['QUERY_STRING'] = http_build_query($params);
        }
        // check that $animal is a string and just a directory name and not a path
        if (!is_string($animal) || strpbrk($animal, '\\/') !== false)
            nice_die('Sorry! Invalid animal name!');
        if (!is_dir($farm . '/' . $animal))
            nice_die("Sorry! This Wiki doesn't exist!");
        if (!defined('DOKU_FARM')) define('DOKU_FARM', $mode);
        return $farm . '/' . $animal . '/conf/';
    }

    // virtual host based
    $uri = explode('/', $_SERVER['SCRIPT_NAME'] ?: $_SERVER['SCRIPT_FILENAME']);
    $server = explode('.', implode('.', array_reverse(explode(':', rtrim($_SERVER['HTTP_HOST'], '.')))));
    for ($i = count($uri) - 1; $i > 0; $i--) {
        for ($j = count($server); $j > 0; $j--) {
            $dir = implode('.', array_slice($server, -$j)) . implode('.', array_slice($uri, 0, $i));
            if (is_dir("$farm/$dir/conf/")) {
                if (!defined('DOKU_FARM')) define('DOKU_FARM', 'virtual');
                return "$farm/$dir/conf/";
            }
        }
    }

    // default conf directory in farm
    if (is_dir("$farm/default/conf/")) {
        if (!defined('DOKU_FARM')) define('DOKU_FARM', 'default');
        return "$farm/default/conf/";
    }
    // farmer
    return DOKU_INC . 'conf/';
}

/* Use default config files and local animal config files */
$config_cascade = [
    'main' => [
        'default' => [DOKU_INC . 'conf/dokuwiki.php'],
        'local' => [DOKU_CONF . 'local.php'],
        'protected' => [DOKU_CONF . 'local.protected.php']
    ],
    'acronyms' => [
        'default' => [DOKU_INC . 'conf/acronyms.conf'],
        'local' => [DOKU_CONF . 'acronyms.local.conf']
    ],
    'entities' => [
        'default' => [DOKU_INC . 'conf/entities.conf'],
        'local' => [DOKU_CONF . 'entities.local.conf']
    ],
    'interwiki' => [
        'default' => [DOKU_INC . 'conf/interwiki.conf'],
        'local' => [DOKU_CONF . 'interwiki.local.conf']
    ],
    'license' => [
        'default' => [DOKU_INC . 'conf/license.php'],
        'local' => [DOKU_CONF . 'license.local.php']
    ],
    'mediameta' => [
        'default' => [DOKU_INC . 'conf/mediameta.php'],
        'local' => [DOKU_CONF . 'mediameta.local.php']
    ],
    'mime' => [
        'default' => [DOKU_INC . 'conf/mime.conf'],
        'local' => [DOKU_CONF . 'mime.local.conf']
    ],
    'scheme' => [
        'default' => [DOKU_INC . 'conf/scheme.conf'],
        'local' => [DOKU_CONF . 'scheme.local.conf']
    ],
    'smileys' => [
        'default' => [DOKU_INC . 'conf/smileys.conf'],
        'local' => [DOKU_CONF . 'smileys.local.conf']
    ],
    'wordblock' => [
        'default' => [DOKU_INC . 'conf/wordblock.conf'],
        'local' => [DOKU_CONF . 'wordblock.local.conf']
    ],
    'acl' => [
        'default' => DOKU_CONF . 'acl.auth.php'
    ],
    'plainauth.users' => [
        'default' => DOKU_CONF . 'users.auth.php'
    ],
    'plugins' => [
        // needed since Angua
        'default' => [DOKU_INC . 'conf/plugins.php'],
        'local' => [DOKU_CONF . 'plugins.local.php'],
        'protected' => [DOKU_INC . 'conf/plugins.required.php', DOKU_CONF . 'plugins.protected.php'],
    ],
    'userstyle' => [
        'screen' => [DOKU_CONF . 'userstyle.css', DOKU_CONF . 'userstyle.less'],
        'print' => [DOKU_CONF . 'userprint.css', DOKU_CONF . 'userprint.less'],
        'feed' => [DOKU_CONF . 'userfeed.css', DOKU_CONF . 'userfeed.less'],
        'all' => [DOKU_CONF . 'userall.css', DOKU_CONF . 'userall.less']
    ],
    'userscript' => [
        'default' => [DOKU_CONF . 'userscript.js']
    ]
];
