/**
 * Scroll to the end of the log on load
 */
jQuery(function () {
    var $dl = jQuery('#plugin__logviewer').find('dl');
    if (!$dl.length) return;
    $dl.animate({scrollTop: $dl.prop("scrollHeight")}, 500);


    var $filter = jQuery('<input>');
    $filter.on('keyup', function (e) {
        var re = new RegExp($filter.val(), 'i');

        $dl.find('dt').each(function (idx, elem) {
            if (elem.innerText.match(re)) {
                jQuery(elem).removeClass('hidden');
            } else {
                jQuery(elem).addClass('hidden');
            }
        });
    });
    $dl.before($filter);
    $filter.wrap('<label></label>');
    $filter.before(LANG.plugins.logviewer.filter + ' ');
});
