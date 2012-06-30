function tpl_dokuwiki_mobile(){
    // check if we are in mobile or tablet mode be sure to adjust the number
    // here when adjusting it in the css
    var $handle = jQuery('#dokuwiki__aside h3.toggle');
    var $toc = jQuery('#dw__toc h3');
    if(document.body.clientWidth > 979) {
        console.log('desktop');
        // reset for desktop mode
        $handle[0].setState(1);
        $handle.hide();
        $toc[0].setState(1);
    } else {
        console.log('mobile');
        // toc and sidebar hiding
        $handle.show();
        $handle[0].setState(-1);
        $toc[0].setState(-1);
    }
}

jQuery(function(){
    var resizeTimer;
    dw_page.makeToggle('#dokuwiki__aside h3.toggle','#dokuwiki__aside div.content');

    tpl_dokuwiki_mobile();
    jQuery(window).bind('resize',
        function(){
            if (resizeTimer) clearTimeout(resizeTimer);
            resizeTimer = setTimeout(tpl_dokuwiki_mobile,200);
        }
    );
});
