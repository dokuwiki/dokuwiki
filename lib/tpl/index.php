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
if(!defined('DOKU_INC')) define('DOKU_INC',dirname(__FILE__).'/../../');
if(!defined('NOSESSION')) define('NOSESSION',1);
require_once(DOKU_INC.'inc/init.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
 "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en" dir="ltr">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Template Replacements</title>
    <style type="text/css">
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
$ini = @parse_ini_file($conf['template'].'/style.ini',true);
if ($ini) {
    echo '<table>';
    echo "<caption>".htmlspecialchars($conf['template'])."'s style.ini</caption>";
    foreach($ini['replacements'] as $key => $val){
        echo '<tr>';
        echo '<td>'.htmlspecialchars($key).'</td>';
        echo '<td>'.htmlspecialchars($val).'</td>';
        echo '<td>';
        if(preg_match('/^#[0-f]{3,6}$/i',$val)){
            echo '<div class="color" style="background-color:'.$val.';">&nbsp;</div>';
        }
        echo '</td>';
        echo '</tr>';
    }
    echo '</table>';
} else {
    echo "<p>Non-existent template: <strong>".htmlspecialchars($conf['template'])."</strong></p>";
}
?>
</body>
</html>
