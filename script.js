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
    jQuery('button.struct_img').click(function () {
        var input_id = jQuery(this).siblings('input').attr('id');
        window.open(
            DOKU_BASE + 'lib/exe/mediamanager.php' +
            '?ns=' + encodeURIComponent(JSINFO['namespace']) +
            '&edid=' + encodeURIComponent(input_id) +
            '&onselect=insertStructImage',
            'mediaselect',
            'width=750,height=500,left=20,top=20,scrollbars=yes,resizable=yes'); //
    });

    /**
     * Custom onSelect handler for struct img button
     */
    window.insertStructImage = function (edid, mediaid, opts, align) {
        jQuery('#' + edid).val(mediaid).change();
    };

    /**
     * Duplicate the elements in .newtemplate whenever any input in it changes
     */
    jQuery('#dw__editform').find('.struct .newtemplate').each(function () {
        var $tplwrapper = jQuery(this);
        var $tpl = $tplwrapper.children().clone(true, true);

        $tplwrapper.on('change', 'input,textarea,select', function () {
            if (jQuery(this).val() == '') return;

            // prepare a new template and make sure all the IDs in it are unique
            var $copy = $tpl.clone(true, true);
            copycount++;
            $copy.find('*[id]').each(function() {
                this.id = this.id + '_' + copycount;
            });

            // append the template
            $tplwrapper.append($copy);
        });
    });

    /**
     * Toggle the disabled class in the schema editor
     */
    jQuery('#plugin__struct').find('td.isenabled input').change(function() {
        var $checkbox = jQuery(this);
        $checkbox.parents('tr').toggleClass('disabled', !$checkbox.prop('checked'));
    });

});
