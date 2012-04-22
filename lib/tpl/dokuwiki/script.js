jQuery(function(){
    // check if we are in mobile mode
    if(jQuery('div.mobileTools').css('display') == 'none') return;

    // toc and sidebar hiding
    dw_page.makeToggle('#dokuwiki__aside h3.toggle','#dokuwiki__aside div.content');

    jQuery('#dw__toc h3.toggle').click();
    jQuery('#dokuwiki__aside h3.toggle').show().click();
});
