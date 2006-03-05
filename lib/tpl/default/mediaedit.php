<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
 "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<?php
/**
 * DokuWiki Default Template
 *
 * This is the template for editing image meta data.
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

  <h1><?php echo hsc($lang['metaedit'])?> <code><?php echo hsc(noNS($IMG))?></code></h1>

  <div class="mediaedit">
    <?php/* everything in meta array is tried to save and read */?>

    <div class="data">
      <form action="<?php echo DOKU_BASE?>lib/exe/media.php" accept-charset="utf-8" method="post">
        <input type="hidden" name="edit" value="<?php echo hsc($IMG)?>" />
        <input type="hidden" name="save" value="1" />

        <label class="block" for="img__title"><?php echo $lang['img_title']?></label>
        <input type="text" name="meta[Iptc.Headline]" id="img__title" class="edit"
         value="<?php echo hsc(tpl_img_getTag('IPTC.Headline'))?>" /><br />

        <label class="block" for="img__caption"><?php echo $lang['img_caption']?></label>
        <textarea name="meta[Iptc.Caption]" id="img__caption" class="edit" rows="5"><?php
          echo hsc(tpl_img_getTag(array('IPTC.Caption',
                                        'EXIF.UserComment',
                                        'EXIF.TIFFImageDescription',
                                        'EXIF.TIFFUserComment')));
        ?></textarea><br />

        <label class="block" for="img__artist"><?php echo $lang['img_artist']?></label>
        <input type="text" name="meta[Iptc.Byline]" id="img__artist" class="edit"
         value="<?php echo hsc(tpl_img_getTag(array('Iptc.Byline',
                                                    'Exif.TIFFArtist',
                                                    'Exif.Artist',
                                                    'Iptc.Credit')))?>" /><br />

        <label class="block" for="img__copy"><?php echo $lang['img_copyr']?></label>
        <input type="text" name="meta[Iptc.CopyrightNotice]" id="img__copy" class="edit"
         value="<?php echo hsc(tpl_img_getTag(array('Iptc.CopyrightNotice','Exif.TIFFCopyright','Exif.Copyright')))?>" /><br />


        <label class="block" for="img__keywords"><?php echo $lang['img_keywords']?></label>
        <textarea name="meta[Iptc.Keywords]" id="img__keywords" class="edit"><?php
          echo hsc(tpl_img_getTag(array('IPTC.Keywords',
                                        'EXIF.Category')));
        ?></textarea><br />


        <input type="submit" value="<?php echo $lang['btn_save']?>" title="ALT+S"
         accesskey="s" class="button" />

      </form>
    </div>


    <div class="footer">
      <hr />
        <?php tpl_button('backtomedia')?>
    </div>
  </div>

</div>
</body>
</html>
