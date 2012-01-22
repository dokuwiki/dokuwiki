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

$showTools = !tpl_getConf('hideTools') || ( tpl_getConf('hideTools') && $_SERVER['REMOTE_USER'] );
$showSidebar = tpl_getConf('sidebarID') && page_exists(tpl_getConf('sidebarID')) && ($ACT=='show');
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
 "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $conf['lang']?>"
 lang="<?php echo $conf['lang']?>" dir="<?php echo $lang['direction'] ?>">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
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
    <?php /* with these Conditional Comments you can better address IE issues in CSS files,
             precede CSS rules by #IE7 for IE7 and #IE8 for IE8 (div closes at the bottom) */ ?>
    <!--[if lte IE 7 ]><div id="IE7"><![endif]--><!--[if IE 8 ]><div id="IE8"><![endif]-->

    <?php /* the "dokuwiki__top" id is needed somewhere at the top, because that's where the "back to top" button/link links to */ ?>
    <?php /* classes mode_<action> are added to make it possible to e.g. style a page differently if it's in edit mode,
         see http://www.dokuwiki.org/devel:action_modes for a list of action modes */ ?>
    <?php /* .dokuwiki should always be in one of the surrounding elements (e.g. plugins and templates depend on it) */ ?>
    <div id="dokuwiki__site"><div id="dokuwiki__top"
        class="dokuwiki site mode_<?php echo $ACT ?> <?php echo ($showSidebar) ? 'hasSidebar' : ''; ?>">

        <?php include('tpl_header.php') ?>

        <div class="wrapper group" id="dokuwiki__detail">

            <!-- ********** CONTENT ********** -->
            <div id="dokuwiki__content"><div class="pad group">
                <?php tpl_flush() /* flush the output buffer */ ?>
                <?php _tpl_include('pageheader.html') ?>

                <!-- ********** IMG DETAIL ********** -->

                <?php if($ERROR){ print $ERROR; }else{ ?>

                    <h1><?php echo hsc(tpl_img_getTag('IPTC.Headline',$IMG))?></h1>

                    <?php tpl_img(900,700); /* parameters: maximum width, maximum height (and more) */ ?>

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

                <!-- ********** /IMG DETAIL ********** -->

                <?php tpl_flush() ?>
                <?php _tpl_include('pagefooter.html') ?>
            </div></div><!-- /content -->
            <hr class="a11y" />

        </div><!-- /wrapper -->

        <?php include('tpl_footer.php') ?>
    </div></div><!-- /site -->

    <!--[if ( IE lte 7 | IE 8 ) ]></div><![endif]-->
</body>
</html>
