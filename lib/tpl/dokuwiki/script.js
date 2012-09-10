/**
 *  We handle several device classes based on browser width.
 *  see http://twitter.github.com/bootstrap/scaffolding.html#responsive
 *
 *  - desktop:   980+
 *  - mobile:    < 980
 *    - tablet   481 - 979   (ostensibly for tablets in portrait mode)
 *    - phone    <= 480
 */
var device_class = ''; // not yet known
var device_classes = 'desktop mobile tablet phone';

function tpl_dokuwiki_mobile(){

    // determine our device pattern
    // TODO: consider moving into dokuwiki core
    var w = document.body.clientWidth;
    if (w > 979) {
        if (device_class == 'desktop') return;
        device_class = 'desktop';
    } else if (w > 480) {
        if (device_class.match(/tablet/)) return;
        device_class = 'mobile tablet';
    } else {
        if (device_class.match(/phone/)) return;
        device_class = 'mobile phone';
    }

    jQuery('html').removeClass(device_classes).addClass(device_class);

    // handle some layout changes based on change in device
    var $handle = jQuery('#dokuwiki__aside h3.toggle');
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
    dw_page.makeToggle('#dokuwiki__aside h3.toggle','#dokuwiki__aside div.content');

    tpl_dokuwiki_mobile();
    jQuery(window).bind('resize',
        function(){
            if (resizeTimer) clearTimeout(resizeTimer);
            resizeTimer = setTimeout(tpl_dokuwiki_mobile,200);
        }
    );

    // increase sidebar length to match content (desktop mode only)
    var $sidebar = jQuery('.desktop #dokuwiki__aside');
    if($sidebar.length) {
        var $content = jQuery('#dokuwiki__content div.page');
        $content.css('min-height', $sidebar.height());
    }
});
