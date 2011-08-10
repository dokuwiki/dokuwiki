<?php
/**
 * DokuWiki Media Manager Popup
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
 "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $conf['lang']?>"
  lang="<?php echo $conf['lang']?>" dir="<?php echo $lang['direction'] ?>" class="popup">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>
        <?php echo hsc($lang['mediaselect'])?>
        [<?php echo strip_tags($conf['title'])?>]
    </title>
    <?php tpl_metaheaders()?>
    <link rel="shortcut icon" href="<?php echo ml('favicon.ico') ?>" />
</head>

<body>
    <!--[if IE 6 ]><div id="IE6"><![endif]--><!--[if IE 7 ]><div id="IE7"><![endif]--><!--[if IE 8 ]><div id="IE8"><![endif]-->
    <div id="media__manager" class="dokuwiki">
        <?php html_msgarea() ?>
        <div id="mediamgr__aside"><div class="pad">
            <h1><?php echo hsc($lang['mediaselect'])?></h1>

            <?php /* keep the id! additional elements are inserted via JS here */?>
            <div id="media__opts"></div>

            <?php tpl_mediaTree() ?>
        </div></div>

        <div id="mediamgr__content"><div class="pad">
            <?php tpl_mediaContent() ?>
        </div></div>
    </div>
    <!--[if ( IE 6 | IE 7 | IE 8 ) ]></div><![endif]-->
</body>
</html>
