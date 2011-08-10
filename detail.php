<?php
/**
 * DokuWiki Image Detail Page
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Anika Henke <anika@selfthinker.org>
 */

// must be run from within DokuWiki
if (!defined('DOKU_INC')) die();

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
 "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $conf['lang']?>"
 lang="<?php echo $conf['lang']?>" dir="<?php echo $lang['direction'] ?>">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>
        <?php echo hsc(tpl_img_getTag('IPTC.Headline',$IMG))?>
        [<?php echo strip_tags($conf['title'])?>]
    </title>
    <?php tpl_metaheaders()?>
    <link rel="shortcut icon" href="<?php echo ml('favicon.ico') ?>" />
</head>

<body>
    <!--[if IE 6 ]><div id="IE6"><![endif]--><!--[if IE 7 ]><div id="IE7"><![endif]--><!--[if IE 8 ]><div id="IE8"><![endif]-->
    <div id="dokuwiki__detail" class="dokuwiki">
        <?php html_msgarea() ?>

        <?php if($ERROR){ print $ERROR; }else{ ?>

            <h1><?php echo hsc(tpl_img_getTag('IPTC.Headline',$IMG))?></h1>

            <div class="content">
                <?php tpl_img(900,700) ?>

                <div class="img_detail">
                    <h2><?php print nl2br(hsc(tpl_img_getTag('simple.title'))); ?></h2>

                    <dl>
                        <?php
                            $t = tpl_img_getTag('Date.EarliestTime');
                            if($t) print '<dt>'.$lang['img_date'].':</dt><dd>'.dformat($t).'</dd>';

                            $t = tpl_img_getTag('File.Name');
                            if($t) print '<dt>'.$lang['img_fname'].':</dt><dd>'.hsc($t).'</dd>';

                            $t = tpl_img_getTag(array('Iptc.Byline','Exif.TIFFArtist','Exif.Artist','Iptc.Credit'));
                            if($t) print '<dt>'.$lang['img_artist'].':</dt><dd>'.hsc($t).'</dd>';

                            $t = tpl_img_getTag(array('Iptc.CopyrightNotice','Exif.TIFFCopyright','Exif.Copyright'));
                            if($t) print '<dt>'.$lang['img_copyr'].':</dt><dd>'.hsc($t).'</dd>';

                            $t = tpl_img_getTag('File.Format');
                            if($t) print '<dt>'.$lang['img_format'].':</dt><dd>'.hsc($t).'</dd>';

                            $t = tpl_img_getTag('File.NiceSize');
                            if($t) print '<dt>'.$lang['img_fsize'].':</dt><dd>'.hsc($t).'</dd>';

                            $t = tpl_img_getTag('Simple.Camera');
                            if($t) print '<dt>'.$lang['img_camera'].':</dt><dd>'.hsc($t).'</dd>';

                            $t = tpl_img_getTag(array('IPTC.Keywords','IPTC.Category','xmp.dc:subject'));
                            if($t) print '<dt>'.$lang['img_keywords'].':</dt><dd>'.hsc($t).'</dd>';

                        ?>
                    </dl>
                    <?php //Comment in for Debug// dbg(tpl_img_getTag('Simple.Raw'));?>
                </div>
                <div class="clearer"></div>
            </div><!-- /.content -->

            <p class="back">&larr; <?php echo $lang['img_backto']?> <?php tpl_pagelink($ID)?></p>

        <?php } ?>
    </div>
    <!--[if ( IE 6 | IE 7 | IE 8 ) ]></div><![endif]-->
</body>
</html>

