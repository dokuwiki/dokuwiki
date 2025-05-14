<?php

/**
 * This file reads the style.ini of the used template and displays the
 * replacements defined in it. Color replacements will be displayed
 * visually. This should help with adjusting and using the styles
 * specified in the style.ini
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Anika Henke <anika@selfthinker.org>
 */

// phpcs:disable PSR1.Files.SideEffects
if (!defined('DOKU_INC')) define('DOKU_INC', __DIR__ . '/../../');
if (!defined('NOSESSION')) define('NOSESSION', 1);
require_once(DOKU_INC . 'inc/init.php');
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Template Replacements</title>
    <style>
        body {
            background-color: #fff;
            color: #000;
        }
        caption {
            font-weight: bold;
        }
        td {
            margin: 0;
            padding: 0.5em 2em;
            font-family: monospace;
            font-size: 120%;
            border: 1px solid #fff;
        }
        tr:hover td {
            border: 1px solid #ccc;
        }
        .color {
            padding: 0.25em 1em;
            border: 1px #000 solid;
        }
    </style>
</head>
<body>
<?php
// get merged style.ini
$styleUtils = new \dokuwiki\StyleUtils($conf['template']);
$ini = $styleUtils->cssStyleini();

if (!empty($ini)) {
    echo '<table>';
    echo "<caption>" . hsc($conf['template']) . "'s style.ini</caption>";
    foreach ($ini['replacements'] as $key => $val) {
        echo '<tr>';
        echo '<td>' . hsc($key) . '</td>';
        echo '<td>' . hsc($val) . '</td>';
        echo '<td>';
        if (preg_match('/^#[0-f]{3,6}$/i', $val)) {
            echo '<div class="color" style="background-color:' . $val . ';">&#160;</div>';
        }
        echo '</td>';
        echo '</tr>';
    }
    echo '</table>';
} else {
    echo "<p>Non-existent or invalid template or style.ini: <strong>" . hsc($conf['template']) . "</strong></p>";
}
?>
</body>
</html>
