function tpl_dokuwiki_mobile(){
    // check if we are in mobile or tablet mode be sure to adjust the number
    // here when adjusting it in the css
    if(document.body.clientWidth > 979) return;

    // toc and sidebar hiding
    dw_page.makeToggle('#dokuwiki__aside h3.toggle','#dokuwiki__aside div.content');

    jQuery('#dw__toc h3.toggle').click();
    jQuery('#dokuwiki__aside h3.toggle').show().click();
}

jQuery(tpl_dokuwiki_mobile);
jQuery(window).bind('resize',tpl_dokuwiki_mobile);
