<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
 "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<?php
/**
 * DokuWiki Default Template
 *
 * This is the template you need to change for the overall look
 * of DokuWiki.
 *
 * You should leave the doctype at the very top - It should
 * always be the very first line of a document.
 *
 * @link   http://wiki.splitbrain.org/wiki:tpl:templates
 * @author Andreas Gohr <andi@splitbrain.org>
 */
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $conf['lang']?>"
 lang="<?php echo $conf['lang']?>" dir="<?php echo $lang['direction']?>">
<head>
  <title><?php tpl_pagetitle()?> [<?php echo hsc($conf['title'])?>]</title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

  <?php tpl_metaheaders()?>

  <link rel="shortcut icon" href="<?php echo DOKU_BASE?>lib/images/favicon.ico" />

  <?php /*old includehook*/ @include(dirname(__FILE__).'/meta.html')?>
</head>

<body>
<?php /*old includehook*/ @include(dirname(__FILE__).'/topheader.html')?>
<div class="dokuwiki">
  <?php html_msgarea()?>

  <div class="stylehead">

    <div class="header">
      <div class="pagename">
        [[<?php tpl_link(wl($ID,'do=backlink'),$ID)?>]]
      </div>
      <div class="logo">
        <?php tpl_link(wl(),$conf['title'],'name="top" accesskey="h" title="[ALT+H]"')?>
      </div>
    </div>
  
    <?php /*old includehook*/ @include(dirname(__FILE__).'/header.html')?>

    <div class="bar" id="bar_top">
      <div class="bar-left" id="bar_topleft">
        <?php tpl_button('edit')?>
        <?php tpl_button('history')?>
      </div>
  
      <div class="bar-right" id="bar_topright">
        <?php tpl_button('recent')?>
        <?php tpl_searchform()?>&nbsp;
      </div>
    </div>

    <?php if($conf['breadcrumbs']){?>
    <div class="breadcrumbs">
      <?php tpl_breadcrumbs()?>
      <?php //tpl_youarehere() //(some people prefer this)?>
    </div>
    <?php }?>

  </div>
  <?php flush()?>

  <?php /*old includehook*/ @include(dirname(__FILE__).'/pageheader.html')?>

  <div class="page">
    <!-- wikipage start -->
    <?php tpl_content()?>
    <!-- wikipage stop -->
  </div>

  <div class="clearer">&nbsp;</div>

  <?php flush()?>

  <div class="stylefoot">

    <div class="meta">
      <div class="user">
        <?php tpl_userinfo()?>
      </div>
      <div class="doc">
        <?php tpl_pageinfo()?>
      </div>
    </div>

   <?php /*old includehook*/ @include(dirname(__FILE__).'/pagefooter.html')?>

    <div class="bar" id="bar_bottom">
      <div class="bar-left" id="bar_bottomleft">
        <?php tpl_button('edit')?>
        <?php tpl_button('history')?>
      </div>
      <div class="bar-right" id="bar_bottomright">
        <?php tpl_button('subscription')?>
        <?php tpl_button('admin')?>
		<?php tpl_button('profile')?>
        <?php tpl_button('login')?>
        <?php tpl_button('index')?>
        <?php tpl_button('top')?>&nbsp;
      </div>
    </div>

  </div>

</div>
<?php /*old includehook*/ @include(dirname(__FILE__).'/footer.html')?>

<?php tpl_indexerWebBug()?>
</body>
</html>
