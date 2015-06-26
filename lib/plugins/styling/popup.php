<?php
if(!defined('DOKU_INC')) define('DOKU_INC', dirname(__FILE__) . '/../../../');
require_once(DOKU_INC . 'inc/init.php');
//close session
session_write_close();
header('Content-Type: text/html; charset=utf-8');

/** @var admin_plugin_styling $plugin */
$plugin = plugin_load('admin', 'styling');
if(!auth_isadmin()) die('only admins allowed');
$plugin->ispopup = true;

// handle posts
$plugin->handle();

// output plugin in a very minimal template:
?>
<html>
<head>
    <title><?php echo $plugin->getLang('menu') ?></title>
    <?php tpl_metaheaders(false) ?>
</head>
<body>
    <div class="dokuwiki page">
        <?php $plugin->html() ?>
    </div>
</body>
</html>
