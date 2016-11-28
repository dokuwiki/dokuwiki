(function () {
    'use strict';

    /**
     * rescale the images so that images that are loaded with the same width are rescaled to contain
     * the same number of pixels (initial width^2) but keep their aspect ratio. This must be done in js, because
     * the server does not know the dimensions of the images used.
     */
    jQuery('.structcloud .struct_media img').each(function (index, element) {
        jQuery(element).on('load', function (event) {
            const $image = jQuery(element);
            const initialWidth = $image.width();
            const ratio = initialWidth / $image.height();
            const area = initialWidth * initialWidth;
            $image.css('width', Math.sqrt(area * ratio));
            $image.css('height', Math.sqrt(area / ratio));
        });
    });
})();
