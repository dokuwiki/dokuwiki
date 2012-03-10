<!-- ********** FOOTER ********** -->
<div id="dokuwiki__footer"><div class="pad">
        <?php
            $tgt = ($conf['target']['extern']) ? 'target="'.$conf['target']['extern'].'"' : '';
            tpl_license(''); // text only
        ?>

    <div class="footerbuttons">

        <?php tpl_license('button',true,false,false); // button, no wrapper?>
        <a <?php echo $tgt?> href="http://www.dokuwiki.org/donate" title="Donate"><img src="<?php echo tpl_basedir(); ?>images/button-donate.gif" alt="Donate" width="80" height="15" /></a>
        <a <?php echo $tgt?> href="http://www.php.net" title="Powered by PHP"><img src="<?php echo tpl_basedir(); ?>images/button-php.gif" width="80" height="15" alt="Powered by PHP" /></a>
        <a <?php echo $tgt?> href="http://validator.w3.org/check/referer" title="Valid XHTML 1.0"><img src="<?php echo tpl_basedir(); ?>images/button-xhtml.png" width="80" height="15" alt="Valid XHTML 1.0" /></a>
        <a <?php echo $tgt?> href="http://jigsaw.w3.org/css-validator/check/referer?profile=css3" title="Valid CSS"><img src="<?php echo tpl_basedir(); ?>images/button-css.png" width="80" height="15" alt="Valid CSS" /></a>
        <a <?php echo $tgt?> href="http://dokuwiki.org/" title="Driven by DokuWiki"><img src="<?php echo tpl_basedir(); ?>images/button-dw.png" width="80" height="15" alt="Driven by DokuWiki" /></a>
    </div>
</div></div><!-- /footer -->

<?php _tpl_include('footer.html') ?>
