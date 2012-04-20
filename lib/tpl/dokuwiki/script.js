jQuery(function(){
    // check if we are in mobile mode
    if(jQuery('div.mobileTools').css('display') == 'none') return;

    // toc and sidebar hiding
    dw_page.makeToggle('#dokuwiki__aside h3.aside','#dokuwiki__aside div.aside');
    jQuery('#dw__toc > h3').click();
    jQuery('#dokuwiki__aside h3.aside').removeClass('a11y').click();
});
