<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
 "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<?php
/**
 * DokuWiki Default Template
 *
 * This is the template for displaying references to a media file.
 * It is displayed in the media popup.
 *
 * You should leave the doctype at the very top - It should
 * always be the very first line of a document.
 *
 * @link   http://wiki.splitbrain.org/wiki:tpl:templates
 * @author Andreas Gohr <andi@splitbrain.org>
 */
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $conf['lang']?>" lang="<?php echo $conf['lang']?>" dir="ltr">
<head>
  <title><?php echo hsc($lang['mediaselect'])?> [<?php echo hsc($conf['title'])?>]</title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

  <?php tpl_metaheaders()?>

  <link rel="shortcut icon" href="<?php echo DOKU_BASE?>images/favicon.ico" />
  <link rel="stylesheet" media="screen" type="text/css" href="<?php echo DOKU_TPL?>layout.css" />
  <link rel="stylesheet" media="screen" type="text/css" href="<?php echo DOKU_TPL?>design.css" />

  <!--[if gte IE 5]>
  <style type="text/css">
    /* that IE 5+ conditional comment makes this only visible in IE 5+ */
    /* IE bugfix for transparent PNGs */
    //img { behavior: url("<?php echo DOKU_BASE?>/pngbehavior.htc"); }
  </style>
  <![endif]-->
</head>

<body>
<div class="dokuwiki">
  <?php html_msgarea()?>

  <h1><?php echo hsc($lang['reference'])?> <code><?php echo hsc(noNS($DEL))?></code></h1>

  <div class="mediaref">
    <div class="mediaref_head">
      <p><?php echo hsc($lang['ref_inuse'])?></p>
    </div>
  
    <?php tpl_showreferences($mediareferences)?>
	
	<div class="mediaref_footer">
	  <hr>
      <?php tpl_button('backtomedia')?>
    </div>
  </div>

</div>
</body>
</html>

