/**
 *  We handle several device classes based on browser width.
 *
 *  - desktop:   > __tablet_width__ (as set in style.ini)
 *  - mobile:
 *    - tablet   <= __tablet_width__
 *    - phone    <= __phone_width__
 */
var device_class = ''; // not yet known
var device_classes = 'desktop mobile tablet phone';

function tpl_easywiki_mobile(){

    // the z-index in mobile.css is (mis-)used purely for detecting the screen mode here
    var screen_mode = jQuery('#screen__mode').css('z-index') + '';

    // determine our device pattern
    // TODO: consider moving into easywiki core
    switch (screen_mode) {
        case '1':
            if (device_class.match(/tablet/)) return;
            device_class = 'mobile tablet';
            break;
        case '2':
            if (device_class.match(/phone/)) return;
            device_class = 'mobile phone';
            break;
        default:
            if (device_class == 'desktop') return;
            device_class = 'desktop';
    }

    jQuery('html').removeClass(device_classes).addClass(device_class);

    // handle some layout changes based on change in device
    var $handle = jQuery('#easywiki__aside h3.toggle');
    var $toc = jQuery('#dw__toc h3');

    if (device_class == 'desktop') {
        // reset for desktop mode
        if($handle.length) {
            $handle[0].setState(1);
            $handle.hide();
        }
        if($toc.length) {
            $toc[0].setState(1);
        }
    }
    if (device_class.match(/mobile/)){
        // toc and sidebar hiding
        if($handle.length) {
            $handle.show();
            $handle[0].setState(-1);
        }
        if($toc.length) {
            $toc[0].setState(-1);
        }
    }
}

jQuery(function(){
    var resizeTimer;
    dw_page.makeToggle('#easywiki__aside h3.toggle','#easywiki__aside div.content');

    tpl_easywiki_mobile();
    jQuery(window).on('resize',
        function(){
            if (resizeTimer) clearTimeout(resizeTimer);
            resizeTimer = setTimeout(tpl_easywiki_mobile,200);
        }
    );

    // increase sidebar length to match content (desktop mode only)
    var sidebar_height = jQuery('.desktop #easywiki__aside').height();
    var pagetool_height = jQuery('.desktop #easywiki__pagetools ul:first').height();
    // pagetools div has no height; ul has a height
    var content_min = Math.max(sidebar_height || 0, pagetool_height || 0);

    var content_height = jQuery('#easywiki__content div.page').height();
    if(content_min && content_min > content_height) {
        var $content = jQuery('#easywiki__content div.page');
        $content.css('min-height', content_min);
    }

    // blur when clicked
    jQuery('#easywiki__pagetools div.tools>ul>li>a').on('click', function(){
        this.blur();
    });
});
