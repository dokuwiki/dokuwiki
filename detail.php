<?php
/**
 * DokuWiki Image Detail Page
 *
 * @author   Andreas Gohr <andi@splitbrain.org>
 * @author   Anika Henke <anika@selfthinker.org>
 * @license  GPL 2 (http://www.gnu.org/licenses/gpl.html)
 */

// must be run from within DokuWiki
if (!defined('DOKU_INC')) die();
@require_once(dirname(__FILE__).'/tpl_functions.php'); /* include hook for template functions */

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
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <?php echo tpl_favicon(array('favicon', 'mobile')) ?>
    <?php _tpl_include('meta.html') ?>
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
                            $config_files = getConfigFiles('mediameta');
                            foreach ($config_files as $config_file) {
                                if(@file_exists($config_file)) {
                                    include($config_file);
                                }
                            }

                            foreach($fields as $key => $tag){
                                $t = array();
                                if (!empty($tag[0])) {
                                    $t = array($tag[0]);
                                }
                                if(is_array($tag[3])) {
                                    $t = array_merge($t,$tag[3]);
                                }
                                $value = tpl_img_getTag($t);
                                if ($value) {
                                    echo '<dt>'.$lang[$tag[1]].':</dt><dd>';
                                    if ($tag[2] == 'date') {
                                        echo dformat($value);
                                    } else {
                                        echo hsc($value);
                                    }
                                    echo '</dd>';
                                }
                            }
                        ?>
                    </dl>
                    <?php //Comment in for Debug// dbg(tpl_img_getTag('Simple.Raw'));?>
                </div>
                <div class="clearer"></div>
            </div><!-- /.content -->

            <p class="back">
                <?php
                    $imgNS = getNS($IMG);
                    $authNS = auth_quickaclcheck("$imgNS:*");
                    if (($authNS >= AUTH_UPLOAD) && function_exists('media_managerURL')) {
                        $mmURL = media_managerURL(array('ns' => $imgNS, 'image' => $IMG));
                        echo '<a href="'.$mmURL.'">'.$lang['img_manager'].'</a><br />';
                    }
                ?>
                &larr; <?php echo $lang['img_backto']?> <?php tpl_pagelink($ID)?>
            </p>

        <?php } ?>
    </div>
    <!--[if ( IE 6 | IE 7 | IE 8 ) ]></div><![endif]-->
</body>
</html>

