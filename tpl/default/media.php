<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
 "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<?
/**
 * DokuWiki Default Template
 *
 * This is the template for the media selection popup.
 *
 * You should leave the doctype at the very top - It should
 * always be the very first line of a document.
 *
 * @link   http://wiki.splitbrain.org/wiki:tpl:templates
 * @author Andreas Gohr <andi@splitbrain.org>
 */
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?=$conf['lang']?>" lang="<?=$conf['lang']?>" dir="ltr">
<head>
  <title><?=hsc($lang['mediaselect'])?> [<?=hsc($conf['title'])?>]</title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

  <?tpl_metaheaders()?>

  <link rel="shortcut icon" href="<?=DOKU_BASE?>images/favicon.ico" />
  <link rel="stylesheet" media="screen" type="text/css" href="<?=DOKU_TPL?>layout.css" />
  <link rel="stylesheet" media="screen" type="text/css" href="<?=DOKU_TPL?>design.css" />

  <!--[if gte IE 5]>
  <style type="text/css">
    /* that IE 5+ conditional comment makes this only visible in IE 5+ */
    /* IE bugfix for transparent PNGs */
    //img { behavior: url("<?=DOKU_BASE?>/pngbehavior.htc"); }
  </style>
  <![endif]-->
</head>

<body>
<div class="dokuwiki">
  <?html_msgarea()?>

  <h1><?=hsc($lang['mediaselect'])?> <code><?=hsc($NS)?></code></h1>

  <div class="mediaselect">

    <div class="mediaselect-left">
      <b><a href="<?=DOKU_BASE?>/media.php?ns="><?=hsc($lang['namespaces'])?></a></b>

			<?tpl_medianamespaces()?>
		</div>

    <div class="mediaselect-right">
      <?tpl_mediafilelist()?>

			<div class="uploadform">
			<?tpl_mediauploadform()?>
			</div>
		</div>

  </div>

</div>
</body>
