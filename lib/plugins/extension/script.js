jQuery(function () {

    const $extmgr = jQuery('#extension__manager');

    /**
     * Confirm uninstalling
     */
    $extmgr.on('click', 'button.uninstall', function (e) {
        if (!window.confirm(LANG.plugins.extension.reallydel)) {
            e.preventDefault();
            return false;
        }
        return true;
    });

    /**
     * very simple lightbox
     * @link http://webdesign.tutsplus.com/tutorials/htmlcss-tutorials/super-simple-lightbox-with-css-and-jquery/
     */
    $extmgr.on('click', 'a.extension_screenshot', function (e) {
        e.preventDefault();

        //Get clicked link href
        const image_href = jQuery(this).attr("href");

        // create lightbox if needed
        let $lightbox = jQuery('#plugin__extensionlightbox');
        if (!$lightbox.length) {
            $lightbox = jQuery(
                '<div id="plugin__extensionlightbox"><p>' + LANG.plugins.extension.close + '</p><div></div></div>'
            )
                .appendTo(jQuery('body'))
                .hide()
                .on('click', function () {
                    $lightbox.hide();
                });
        }

        // fill and show it
        $lightbox
            .show()
            .find('div').html('<img src="' + image_href + '" />');

        return false;
    });

    /**
     * Enable/Disable extension via AJAX
     */
    $extmgr.on('click', 'button.disable, button.enable', function (e) {
        e.preventDefault();
        const $btn = jQuery(this);
        const $section = $btn.parents('section');

        // disable while we wait
        $btn.attr('disabled', 'disabled');
        $btn.css('cursor', 'wait');

        // execute
        jQuery.get(
            DOKU_BASE + 'lib/exe/ajax.php',
            {
                call: 'plugin_extension',
                ext: $section.data('ext'),
                act: 'toggle',
                sectok: $btn.parents('form').find('input[name=sectok]').val()
            },
            function (html) {
                $section.replaceWith(html);
            }
        ).fail(function (data) {
            $btn.css('cursor', '').removeAttr('disabled');
            window.alert(data.responseText);
        });
    });


    /**
     Create section for enabling/disabling viewing options
     */
    if ($extmgr.find('.plugins, .templates').hasClass('active')) {
        const $extlist = jQuery('#extension__list');

        const $displayOpts = jQuery('<p>').appendTo($extmgr.find('.panelHeader'));
        const $label = jQuery('<label />').appendTo($displayOpts);
        const $checkbox = jQuery('<input />', {type: 'checkbox'}).appendTo($label);
        $label.append(' ' + LANG.plugins.extension.filter);

        let filter = !! window.localStorage.getItem('ext_filter');
        $checkbox.prop('checked', filter);
        $extlist.toggleClass('filter', filter);

        $checkbox.on('change', function () {
            filter = this.checked;
            window.localStorage.setItem('ext_filter', filter ? '1' : '');
            $extlist.toggleClass('filter', filter);
        });


    }
});
