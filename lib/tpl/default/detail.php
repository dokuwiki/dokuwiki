<?php
/**
 * DokuWiki Image Detail Template
 *
 * This is the template for displaying image details
 *
 * @link   http://dokuwiki.org/templates
 * @author Andreas Gohr <andi@splitbrain.org>
 */

// must be run from within DokuWiki
if (!defined('DOKU_INC')) die();

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
 "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $conf['lang']?>" lang="<?php echo $conf['lang']?>" dir="ltr">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>
     <?php echo hsc(tpl_img_getTag('IPTC.Headline',$IMG))?>
    [<?php echo strip_tags($conf['title'])?>]
  </title>

  <?php tpl_metaheaders()?>

  <link rel="shortcut icon" href="<?php echo DOKU_TPL?>images/favicon.ico" />
</head>

<body>
<div class="dokuwiki">
  <?php html_msgarea()?>

  <div class="page">
    <?php if($ERROR){ print $ERROR; }else{ ?>

    <h1><?php echo hsc(tpl_img_getTag('IPTC.Headline',$IMG))?></h1>

    <div class="img_big">
      <?php tpl_img(900,700) ?>
    </div>

    <div class="img_detail">
      <p class="img_caption">
        <?php print nl2br(hsc(tpl_img_getTag('simple.title'))); ?>
      </p>

      <p>&larr; <?php echo $lang['img_backto']?> <?php tpl_pagelink($ID)?></p>
      <?php
            $imgNS = getNS($IMG);
            $authNS = auth_quickaclcheck("$imgNS:*");
            if ($authNS >= AUTH_UPLOAD) {
                echo '<p><a href="'.media_managerURL(array('ns' => $imgNS, 'image' => $IMG)).'">'.$lang['img_manager'].'</a></p>';
            }
      ?>

      <dl class="img_tags">
        <?php
            $config_files = getConfigFiles('mediameta');
            foreach ($config_files as $config_file) {
                if(@file_exists($config_file)) include($config_file);
            }

            foreach($fields as $key => $tag){
                $t = array();
                if (!empty($tag[0])) $t = array($tag[0]);
                if(is_array($tag[3])) $t = array_merge($t,$tag[3]);
                $value = tpl_img_getTag($t);
                if ($value) {
                    echo '<dt>'.$lang[$tag[1]].':</dt><dd>';
                    if ($tag[2] == 'date') echo dformat($value);
                    else echo hsc($value);
                    echo '</dd>';
                }
            }
        ?>
      </dl>
      <?php //Comment in for Debug// dbg(tpl_img_getTag('Simple.Raw'));?>
    </div>

  <?php } ?>
  </div>
</div>
</body>
</html>

