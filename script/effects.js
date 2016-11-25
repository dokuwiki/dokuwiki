(function() {
    'use strict';

    const $taglist = jQuery('.cloudaggregation ul');
    const $taglis = $taglist.children('li');
    $taglis.sort(function (a, b) {
        const at = jQuery(a).text().toLowerCase();
        const bt = jQuery(b).text().toLowerCase();

        if (at > bt) {
            return 1;
        }

        if (bt < at) {
            return -1;
        }

        return 0;
    });

    $taglis.detach().appendTo($taglist);
})();
