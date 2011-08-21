<?php
/**
 * DokuWiki Starter Template
 *
 * @link   http://dokuwiki.org/template:starter
 * @author Anika Henke <anika@selfthinker.org>
 */

if (!defined('DOKU_INC')) die(); /* must be run from within DokuWiki */
@require_once(dirname(__FILE__).'/tpl_functions.php'); /* include hook for template functions */

$showTools = !tpl_getConf('hideTools') || ( tpl_getConf('hideTools') && $_SERVER['REMOTE_USER'] );
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $conf['lang'] ?>"
  lang="<?php echo $conf['lang'] ?>" dir="<?php echo $lang['direction'] ?>">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title><?php tpl_pagetitle() ?> [<?php echo strip_tags($conf['title']) ?>]</title>
    <?php tpl_metaheaders() ?>
    <link rel="shortcut icon" href="<?php echo _tpl_getFavicon() /* DW versions > 2010-11-12 can use the core function tpl_getFavicon() */ ?>" />
    <?php _tpl_include('meta.html') ?>
</head>

<body>
    <?php /* with these Conditional Comments you can better address IE issues in CSS files,
             precede CSS rules by #IE6 for IE6, #IE7 for IE7 and #IE8 for IE8 (div closes at the bottom) */ ?>
    <!--[if IE 6 ]><div id="IE6"><![endif]--><!--[if IE 7 ]><div id="IE7"><![endif]--><!--[if IE 8 ]><div id="IE8"><![endif]-->

    <?php /* classes mode_<action> are added to make it possible to e.g. style a page differently if it's in edit mode,
         see http://www.dokuwiki.org/devel:action_modes for a list of action modes */ ?>
    <?php /* .dokuwiki should always be in one of the surrounding elements (e.g. plugins and templates depend on it) */ ?>
    <div id="dokuwiki__site"><div class="dokuwiki site mode_<?php echo $ACT ?>">
        <?php html_msgarea() /* occasional error and info messages on top of the page */ ?>
        <?php _tpl_include('header.html') ?>

        <!-- ********** HEADER ********** -->
        <div id="dokuwiki__header"><div class="pad">

            <div class="headings">
                <h1><?php tpl_link(wl(),$conf['title'],'id="dokuwiki__top" accesskey="h" title="[H]"') ?></h1>
                <?php /* how to insert logo instead (if no CSS image replacement technique is used):
                        upload your logo into the data/media folder (root of the media manager) and replace 'logo.png' accordingly:
                        tpl_link(wl(),'<img src="'.ml('logo.png').'" alt="'.$conf['title'].'" />','id="dokuwiki__top" accesskey="h" title="[H]"') */ ?>
                <?php if (tpl_getConf('tagline')): ?>
                    <p class="claim"><?php echo tpl_getConf('tagline') ?></p>
                <?php endif ?>

                <ul class="a11y">
                    <li><a href="#dokuwiki__content"><?php echo tpl_getLang('skip_to_content') ?></a></li>
                </ul>
                <div class="clearer"></div>
            </div>

            <div class="tools">
                <!-- USER TOOLS -->
                <?php if ($conf['useacl'] && $showTools): ?>
                    <div id="dokuwiki__usertools">
                      <div class="inner">
                          <h3 class="a11y"><?php echo tpl_getLang('user_tools') ?></h3>
                          <ul>
                              <?php /* the optional second parameter of tpl_action() switches between a link and a button,
                                       e.g. a button inside a <li> would be: tpl_action('edit',0,'li') */
                                  if ($_SERVER['REMOTE_USER']) {
                                      echo '<li class="user">';
                                      tpl_userinfo(); /* 'Logged in as ...' */
                                      echo '</li>';
                                  }
                                  tpl_action('admin', 1, 'li');
                                  _tpl_action('userpage', 1, 'li');
                                  tpl_action('profile', 1, 'li');
                                  _tpl_action('register', 1, 'li'); /* DW versions > 2011-02-20 can use the core function tpl_action('register', 1, 'li') */
                                  tpl_action('login', 1, 'li');
                              ?>
                          </ul>
                        </div>
                    </div>
                <?php endif ?>

                <!-- SITE TOOLS -->
                <div id="dokuwiki__sitetools">
                    <h3 class="a11y"><?php echo tpl_getLang('site_tools') ?></h3>
                    <?php tpl_searchform() ?>
                    <ul>
                        <?php
                            tpl_action('recent', 1, 'li');
                            tpl_action('index', 1, 'li');
                        ?>
                    </ul>
                </div>

            </div>
            <div class="clearer"></div>

            <div class="clearer"></div>
            <hr class="a11y" />
        </div></div><!-- /header -->


        <div class="wrapper">

            <!-- ********** CONTENT ********** -->
            <div id="dokuwiki__content"><div class="pad">
                <?php tpl_flush() /* flush the output buffer */ ?>
                <?php _tpl_include('pageheader.html') ?>

                <div class="page">

                    <!-- BREADCRUMBS -->
                    <?php if($conf['breadcrumbs']){ ?>
                        <div class="breadcrumbs"><?php tpl_breadcrumbs() ?></div>
                    <?php } ?>
                    <?php if($conf['youarehere']){ ?>
                        <div class="breadcrumbs"><?php tpl_youarehere() ?></div>
                    <?php } ?>

                    <!-- wikipage start -->
                    <?php tpl_content() /* the main content */ ?>
                    <!-- wikipage stop -->
                    <div class="clearer"></div>
                </div>

                <?php tpl_flush() ?>
                <?php _tpl_include('pagefooter.html') ?>
            </div></div><!-- /content -->

            <!-- ********** ASIDE ********** -->
            <div id="dokuwiki__aside"><div class="pad include">
                <?php tpl_include_page(tpl_getConf('sidebarID')) /* includes the given wiki page */ ?>
                <div class="clearer"></div>
            </div></div><!-- /aside -->

            <div class="clearer"></div>
            <hr class="a11y" />

            <!-- PAGE ACTIONS -->
            <?php if ($showTools): ?>
                <div id="dokuwiki__pagetools">
                    <h3 class="a11y"><?php echo tpl_getLang('page_tools') ?></h3>
                    <ul>
                        <?php
                            tpl_action('edit', 1, 'li');
                            _tpl_action('discussion', 1, 'li');
                            tpl_action('history', 1, 'li');
                            tpl_action('backlink', 1, 'li');
                            tpl_action('subscribe', 1, 'li');
                            tpl_action('revert', 1, 'li');
                            tpl_action('top', 1, 'li');
                        ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div><!-- /wrapper -->

        <!-- ********** FOOTER ********** -->
        <div id="dokuwiki__footer"><div class="pad">
            <div class="doc"><?php tpl_pageinfo() /* 'Last modified' etc */ ?></div>
            <?php tpl_license('button') /* content license, parameters: img=*badge|button|0, imgonly=*0|1, return=*0|1 */ ?>
        </div></div><!-- /footer -->

        <?php _tpl_include('footer.html') ?>
    </div></div><!-- /site -->

    <div class="no"><?php tpl_indexerWebBug() /* provide DokuWiki housekeeping, required in all templates */ ?></div>
    <!--[if ( IE 6 | IE 7 | IE 8 ) ]></div><![endif]-->
    <div class="header_background"></div>
</body>
</html>
