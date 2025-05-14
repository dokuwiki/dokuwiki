<?php
// phpcs:disable PSR1.Files.SideEffects
if (!defined('DOKU_INC')) define('DOKU_INC', __DIR__ . '/../../../');
require_once(DOKU_INC . 'inc/init.php');
//close session
session_write_close();
header('Content-Type: text/html; charset=utf-8');
header('X-UA-Compatible: IE=edge,chrome=1');

/** @var admin_plugin_styling $plugin */
$plugin = plugin_load('admin', 'styling');
if (!$plugin->isAccessibleByCurrentUser()) die('only admins allowed');
$plugin->ispopup = true;

// handle posts
$plugin->handle();

// output plugin in a very minimal template:
?><!DOCTYPE html>
<html lang="<?php echo $conf['lang'] ?>" dir="<?php echo $lang['direction'] ?>">
<head>
    <meta charset="utf-8" />
    <title><?php echo $plugin->getLang('menu') ?></title>
    <?php tpl_metaheaders(false) ?>
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <?php echo tpl_favicon(['favicon']) ?>
</head>
<body class="dokuwiki">
    <?php $plugin->html() ?>
</body>
</html>
