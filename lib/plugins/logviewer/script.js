/**
 * Scroll to the end of the log on load
 */
jQuery(function () {
    var $dl = jQuery('#plugin__logviewer').find('dl');
    if(!$dl.length) return;
    $dl.animate({ scrollTop: $dl.prop("scrollHeight")}, 500);
});
