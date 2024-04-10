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
    <title>
        <?php echo hsc(tpl_img_getTag('IPTC.Headline', $IMG))?>
        [<?php echo strip_tags($conf['title'])?>]
    </title>
    <?php tpl_metaheaders()?>
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <?php echo tpl_favicon(['favicon', 'mobile']) ?>
    <?php tpl_includeFile('meta.html') ?>
</head>

<body>
    <div id="dokuwiki__site"><div id="dokuwiki__top" class="site <?php echo tpl_classes(); ?>">

        <?php include(__DIR__ . '/tpl_header.php') ?>

        <div class="wrapper group" id="dokuwiki__detail">

            <!-- ********** CONTENT ********** -->
            <main id="dokuwiki__content"><div class="pad group">
                <?php html_msgarea() ?>

                <?php if (!$ERROR) : ?>
                    <div class="pageId"><span><?php echo hsc(tpl_img_getTag('IPTC.Headline', $IMG)); ?></span></div>
                <?php endif; ?>

                <div class="page group">
                    <?php tpl_flush() ?>
                    <?php tpl_includeFile('pageheader.html') ?>
                    <!-- detail start -->
                    <?php
                    if ($ERROR) :
                        echo '<h1>' . $ERROR . '</h1>';
                    else : ?>
                        <?php if ($REV) echo p_locale_xhtml('showrev');?>
                        <h1><?php echo nl2br(hsc(tpl_img_getTag('simple.title'))); ?></h1>

                        <?php tpl_img(900, 700); /* parameters: maximum width, maximum height (and more) */ ?>

                        <div class="img_detail">
                            <?php tpl_img_meta(); ?>
                            <dl>
                            <?php
                            echo '<dt>' . $lang['reference'] . ':</dt>';
                            $media_usage = ft_mediause($IMG, true);
                            if ($media_usage !== []) {
                                foreach ($media_usage as $path) {
                                    echo '<dd>' . html_wikilink($path) . '</dd>';
                                }
                            } else {
                                echo '<dd>' . $lang['nothingfound'] . '</dd>';
                            }
                            ?>
                            </dl>
                            <p><?php echo $lang['media_acl_warning']; ?></p>
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

            </div></main><!-- /content -->

            <hr class="a11y" />

            <!-- PAGE ACTIONS -->
            <?php if (!$ERROR) : ?>
                <nav id="dokuwiki__pagetools" aria-labelledby="dokuwiki__pagetools__heading">
                    <h3 class="a11y" id="dokuwiki__pagetools__heading"><?php echo $lang['page_tools']; ?></h3>
                    <div class="tools">
                        <ul>
                            <?php echo (new \dokuwiki\Menu\DetailMenu())->getListItems(); ?>
                        </ul>
                    </div>
                </nav>
            <?php endif; ?>
        </div><!-- /wrapper -->

        <?php include(__DIR__ . '/tpl_footer.php') ?>
    </div></div><!-- /site -->
</body>
</html>
