jQuery(function () {
    'use strict';

    /** counter for copied multi templates */
    var copycount = 0;

    /**
     * Attach datepicker to date types
     */
    jQuery('input.struct_date').datepicker({
        dateFormat: 'yy-mm-dd'
    });

    /**
     * Attach image dialog to image types
     */
    jQuery('button.struct_media').click(function () {
        var input_id = jQuery(this).siblings('input').attr('id');
        window.open(
            DOKU_BASE + 'lib/exe/mediamanager.php' +
            '?ns=' + encodeURIComponent(JSINFO['namespace']) +
            '&edid=' + encodeURIComponent(input_id) +
            '&onselect=insertStructMedia',
            'mediaselect',
            'width=750,height=500,left=20,top=20,scrollbars=yes,resizable=yes'); //
    });

    /**
     * Custom onSelect handler for struct img button
     */
    window.insertStructMedia = function (edid, mediaid, opts, align) {
        jQuery('#' + edid).val(mediaid).change();
    };

    /**
     * Toggle the disabled class in the schema editor
     */
    jQuery('#plugin__struct').find('td.isenabled input').change(function () {
        var $checkbox = jQuery(this);
        $checkbox.parents('tr').toggleClass('disabled', !$checkbox.prop('checked'));
    });

    var $dokuform = jQuery('#dw__editform');

    /**
     * Duplicate the elements in .newtemplate whenever any input in it changes
     */
    $dokuform.find('.struct .newtemplate').each(function () {
        var $tplwrapper = jQuery(this);
        var $tpl = $tplwrapper.children().clone(true, true);

        $tplwrapper.on('change', 'input,textarea,select', function () {
            if (jQuery(this).val() == '') return;

            // prepare a new template and make sure all the IDs in it are unique
            var $copy = $tpl.clone(true, true);
            copycount++;
            $copy.find('*[id]').each(function () {
                this.id = this.id + '_' + copycount;
            });

            // append the template
            $tplwrapper.append($copy);
        });
    });

    /**
     * Toggle fieldsets in edit form and remeber in cookie
     */
    $dokuform.find('.struct fieldset legend').each(function () {
        var $legend = jQuery(this);
        var $fset = $legend.parent();

        // reinit saved state from cookie
        if (DokuCookie.getValue($fset.data('schema'))) {
            $fset.toggleClass('closed');
        }

        // attach click handler

        $legend.click(function () {
            $fset.toggleClass('closed');
            // remember setting in preference cookie
            if ($fset.hasClass('closed')) {
                DokuCookie.setValue($fset.data('schema'), 1);
            } else {
                DokuCookie.setValue($fset.data('schema'), '');
            }
        });
    });

});
