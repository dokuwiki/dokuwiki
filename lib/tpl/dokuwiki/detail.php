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

?><!DOCTYPE html>
<html lang="<?php echo $conf['lang']?>" dir="<?php echo $lang['direction'] ?>" class="no-js">
<head>
    <meta charset="utf-8" />
    <!--[if IE]><meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" /><![endif]-->
    <title>
        <?php echo hsc(tpl_img_getTag('IPTC.Headline',$IMG))?>
        [<?php echo strip_tags($conf['title'])?>]
    </title>
    <script>(function(H){H.className=H.className.replace(/\bno-js\b/,'js')})(document.documentElement)</script>
    <?php tpl_metaheaders()?>
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <?php echo tpl_favicon(array('favicon', 'mobile')) ?>
    <?php tpl_includeFile('meta.html') ?>
</head>

<body>
    <!--[if lte IE 7 ]><div id="IE7"><![endif]--><!--[if IE 8 ]><div id="IE8"><![endif]-->
    <div id="dokuwiki__site"><div id="dokuwiki__top"
        class="dokuwiki site mode_<?php echo $ACT ?>">

        <?php include('tpl_header.php') ?>

        <div class="wrapper group" id="dokuwiki__detail">

            <!-- ********** CONTENT ********** -->
            <div id="dokuwiki__content"><div class="pad group">

                <?php if(!$ERROR): ?>
                    <div class="pageId"><span><?php echo hsc(tpl_img_getTag('IPTC.Headline',$IMG)); ?></span></div>
                <?php endif; ?>

                <div class="page group">
                    <?php tpl_flush() ?>
                    <?php tpl_includeFile('pageheader.html') ?>
                    <!-- detail start -->
                    <?php
                    if($ERROR):
                        echo '<h1>'.$ERROR.'</h1>';
                    else: ?>

                        <h1><?php echo nl2br(hsc(tpl_img_getTag('simple.title'))); ?></h1>

                        <?php tpl_img(900,700); /* parameters: maximum width, maximum height (and more) */ ?>

                        <div class="img_detail">
                            <dl>
                                <?php
                                    // @todo: logic should be transferred to backend
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
                        </div>
                        <?php //Comment in for Debug// dbg(tpl_img_getTag('Simple.Raw'));?>
                    <?php endif; ?>
                </div>
                <!-- detail stop -->
                <?php tpl_includeFile('pagefooter.html') ?>
                <?php tpl_flush() ?>

                <?php /* doesn't make sense like this; @todo: maybe add tpl_imginfo()?
                <div class="docInfo"><?php tpl_pageinfo(); ?></div>
                */ ?>

            </div></div><!-- /content -->

            <hr class="a11y" />

            <!-- PAGE ACTIONS -->
            <?php if (!$ERROR): ?>
                <div id="dokuwiki__pagetools">
                    <h3 class="a11y"><?php echo $lang['page_tools']; ?></h3>
                    <div class="tools">
                        <ul>
                            <?php // View in media manager; @todo: transfer logic to backend
                                $imgNS = getNS($IMG);
                                $authNS = auth_quickaclcheck("$imgNS:*");
                                if (($authNS >= AUTH_UPLOAD) && function_exists('media_managerURL')) {
                                    $mmURL = media_managerURL(array('ns' => $imgNS, 'image' => $IMG));
                                    echo '<li><a href="'.$mmURL.'" class="mediaManager"><span>'.$lang['img_manager'].'</span></a></li>';
                                }
                            ?>
                            <?php // Back to [ID]; @todo: transfer logic to backend
                                echo '<li><a href="'.wl($ID).'" class="back"><span>'.$lang['img_backto'].' '.$ID.'</span></a></li>';
                            ?>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>
        </div><!-- /wrapper -->

        <?php include('tpl_footer.php') ?>
    </div></div><!-- /site -->

    <!--[if ( lte IE 7 | IE 8 ) ]></div><![endif]-->
</body>
</html>
