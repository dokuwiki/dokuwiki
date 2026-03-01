<?php
/**
 * This file defines multiple available licenses you can license your
 * wiki contents under. Do not change this file, but create a
 * license.local.php instead.
 */

if(empty($LC)) $LC = empty($conf['lang']) ? 'en' : $conf['lang'];

$license['cc-zero'] = array(
    'name' => 'CC0 1.0 Universal',
    'url'  => 'https://creativecommons.org/publicdomain/zero/1.0/deed.'.$LC,
);
$license['publicdomain'] = array(
    'name' => 'Public Domain',
    'url'  => 'https://creativecommons.org/licenses/publicdomain/deed.'.$LC,
);
$license['cc-by'] = array(
    'name' => 'CC Attribution 4.0 International',
    'url'  => 'https://creativecommons.org/licenses/by/4.0/deed.'.$LC,
);
$license['cc-by-sa'] = array(
    'name' => 'CC Attribution-Share Alike 4.0 International',
    'url'  => 'https://creativecommons.org/licenses/by-sa/4.0/deed.'.$LC,
);
$license['gnufdl'] = array(
    'name' => 'GNU Free Documentation License 1.3',
    'url'  => 'https://www.gnu.org/licenses/fdl-1.3.html',
);
$license['cc-by-nc'] = array(
    'name' => 'CC Attribution-Noncommercial 4.0 International',
    'url'  => 'https://creativecommons.org/licenses/by-nc/4.0/deed.'.$LC,
);
$license['cc-by-nc-sa'] = array(
    'name' => 'CC Attribution-Noncommercial-Share Alike 4.0 International',
    'url'  => 'https://creativecommons.org/licenses/by-nc-sa/4.0/deed.'.$LC,
);

