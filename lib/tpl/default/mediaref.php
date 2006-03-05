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
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title><?php echo hsc($lang['mediaselect'])?> [<?php echo hsc($conf['title'])?>]</title>

  <?php tpl_metaheaders()?>

  <link rel="shortcut icon" href="<?php echo DOKU_TPL?>images/favicon.ico" />

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
    <hr />
      <?php tpl_button('backtomedia')?>
    </div>
  </div>

</div>
</body>
</html>

