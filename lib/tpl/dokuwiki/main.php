<?php
/**
 * DokuWiki Default Template 2012
 *
 * @link     http://dokuwiki.org/template
 * @author   Anika Henke <anika@selfthinker.org>
 * @author   Clarence Lee <clarencedglee@gmail.com>
 * @license  GPL 2 (http://www.gnu.org/licenses/gpl.html)
 */

if (!defined('DOKU_INC')) die(); /* must be run from within DokuWiki */

$hasSidebar = page_findnearest($conf['sidebar']);
$showSidebar = $hasSidebar && ($ACT=='show');
?><!DOCTYPE html>
<html lang="<?php echo $conf['lang'] ?>" dir="<?php echo $lang['direction'] ?>" class="no-js">
<head>
    <meta charset="utf-8" />
    <!--[if IE]><meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" /><![endif]-->
    <title><?php tpl_pagetitle() ?> [<?php echo strip_tags($conf['title']) ?>]</title>
    <script>(function(H){H.className=H.className.replace(/\bno-js\b/,'js')})(document.documentElement)</script>
    <?php tpl_metaheaders() ?>
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <?php echo tpl_favicon(array('favicon', 'mobile')) ?>
    <?php tpl_includeFile('meta.html') ?>
</head>

<body>
    <!--[if lte IE 7 ]><div id="IE7"><![endif]--><!--[if IE 8 ]><div id="IE8"><![endif]-->
    <div id="dokuwiki__site"><div id="dokuwiki__top"
        class="dokuwiki site mode_<?php echo $ACT ?> <?php echo ($showSidebar) ? 'showSidebar' : '';
        ?> <?php echo ($hasSidebar) ? 'hasSidebar' : ''; ?>">

        <?php include('tpl_header.php') ?>

        <div class="wrapper group">

            <?php if($showSidebar): ?>
                <!-- ********** ASIDE ********** -->
                <div id="dokuwiki__aside"><div class="pad include group">
                    <h3 class="toggle"><?php echo $lang['sidebar'] ?></h3>
                    <div class="content">
                        <?php tpl_flush() ?>
                        <?php tpl_includeFile('sidebarheader.html') ?>
                        <?php tpl_sidebar() ?>
                        <?php tpl_includeFile('sidebarfooter.html') ?>
                    </div>
                </div></div><!-- /aside -->
            <?php endif; ?>

            <!-- ********** CONTENT ********** -->
            <div id="dokuwiki__content"><div class="pad group">

                <div class="pageId"><span><?php echo hsc($ID) ?></span></div>

                <div class="page group">
                    <?php tpl_flush() ?>
                    <?php tpl_includeFile('pageheader.html') ?>
                    <!-- wikipage start -->
                    <?php tpl_content() ?>
                    <!-- wikipage stop -->
                    <?php tpl_includeFile('pagefooter.html') ?>
                </div>

                <div class="docInfo"><?php tpl_pageinfo() ?></div>

                <?php tpl_flush() ?>
            </div></div><!-- /content -->

            <hr class="a11y" />

            <!-- PAGE ACTIONS -->
            <div id="dokuwiki__pagetools">
                <h3 class="a11y"><?php echo $lang['page_tools']; ?></h3>
                <div class="tools">
                    <ul>
                        <?php
                            tpl_action('edit',      1, 'li', 0, '<span>', '</span>');
                            tpl_action('revert',    1, 'li', 0, '<span>', '</span>');
                            tpl_action('revisions', 1, 'li', 0, '<span>', '</span>');
                            tpl_action('backlink',  1, 'li', 0, '<span>', '</span>');
                            tpl_action('subscribe', 1, 'li', 0, '<span>', '</span>');
                            tpl_action('top',       1, 'li', 0, '<span>', '</span>');
                        ?>
                    </ul>
                </div>
            </div>
        </div><!-- /wrapper -->

        <?php include('tpl_footer.php') ?>
    </div></div><!-- /site -->

    <div class="no"><?php tpl_indexerWebBug() /* provide DokuWiki housekeeping, required in all templates */ ?></div>
    <!--[if ( lte IE 7 | IE 8 ) ]></div><![endif]-->
</body>
</html>
