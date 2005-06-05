<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
 "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<?
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
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?=$conf['lang']?>"
 lang="<?=$conf['lang']?>" dir="<?=$lang['direction']?>">
<head>
  <title><?=$ID?> [<?=hsc($conf['title'])?>]</title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

  <?tpl_metaheaders()?>

  <link rel="shortcut icon" href="<?=DOKU_BASE?>lib/images/favicon.ico" />
  <link rel="stylesheet" media="screen" type="text/css" href="<?=DOKU_TPL?>layout.css" />
  <link rel="stylesheet" media="screen" type="text/css" href="<?=DOKU_TPL?>design.css" />

  <? if($lang['direction'] == 'rtl') {?>
  <link rel="stylesheet" media="screen" type="text/css" href="<?=DOKU_TPL?>rtl.css" />
  <? } ?>

  <link rel="stylesheet" media="print" type="text/css" href="<?=DOKU_TPL?>print.css" />

  <!--[if gte IE 5]>
  <style type="text/css">
    /* that IE 5+ conditional comment makes this only visible in IE 5+ */
    /* IE bugfix for transparent PNGs */
    //DISABLED   img { behavior: url("<?=DOKU_BASE?>lib/scripts/pngbehavior.htc"); }
  </style>
  <![endif]-->

  <?/*old includehook*/ @include(dirname(__FILE__).'/meta.html')?>
</head>

<body>
<?/*old includehook*/ @include(dirname(__FILE__).'/topheader.html')?>
<div class="dokuwiki">
  <?html_msgarea()?>

  <div class="stylehead">

    <div class="header">
      <div class="pagename">
        [[<?tpl_link(wl($ID,'do=backlink'),$ID)?>]]
      </div>
      <div class="logo">
        <?tpl_link(wl(),$conf['title'],'name="top" accesskey="h" title="[ALT+H]"')?>
      </div>
    </div>
  
    <?/*old includehook*/ @include(dirname(__FILE__).'/header.html')?>

    <div class="bar" id="bar_top">
      <div class="bar-left" id="bar_topleft">
        <?tpl_button('edit')?>
        <?tpl_button('history')?>
      </div>
  
      <div class="bar-right" id="bar_topright">
        <?tpl_button('recent')?>
        <?tpl_searchform()?>&nbsp;
      </div>
    </div>

    <?if($conf['breadcrumbs']){?>
    <div class="breadcrumbs">
      <?tpl_breadcrumbs()?>
      <?//tpl_youarehere() //(some people prefer this)?>
    </div>
    <?}?>

  </div>
  <?flush()?>

  <?/*old includehook*/ @include(dirname(__FILE__).'/pageheader.html')?>

  <div class="page">
    <!-- wikipage start -->
    <?tpl_content()?>
    <!-- wikipage stop -->
  </div>

  <div class="clearer">&nbsp;</div>

  <?flush()?>

  <div class="stylefoot">

    <div class="meta">
      <div class="user">
        <?tpl_userinfo()?>
      </div>
      <div class="doc">
        <?tpl_pageinfo()?>
      </div>
    </div>

   <?/*old includehook*/ @include(dirname(__FILE__).'/pagefooter.html')?>

    <div class="bar" id="bar_bottom">
      <div class="bar-left" id="bar_bottomleft">
        <?tpl_button('edit')?>
        <?tpl_button('history')?>
      </div>
      <div class="bar-right" id="bar_bottomright">
        <?tpl_button('admin')?>
        <?tpl_button('login')?>
        <?tpl_button('index')?>
        <?tpl_button('top')?>&nbsp;
      </div>
    </div>

  </div>

</div>
<?/*old includehook*/ @include(dirname(__FILE__).'/footer.html')?>
</body>
</html>
