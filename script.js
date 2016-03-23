jQuery(function () {
    'use strict';

    /** counter for copied multi templates */
    var copycount = 0;

    /**
     * Simplyfies AJAX requests for types
     *
     * @param {string} column A configured column in the form schema.name
     * @param {function} fn Callback on success
     * @param {object} data Additional data to pass
     */
    function struct_ajax(column, fn, data) {
        if (!data) data = {};

        data['call'] = 'plugin_struct';
        data['column'] = column;

        jQuery.post(DOKU_BASE + 'lib/exe/ajax.php', data, fn, 'json')
            .fail(function (result) {
                if(result.responseJSON.stacktrace) {
                    console.error(result.responseJSON.error + "\n" + result.responseJSON.stacktrace);
                }
                alert(result.responseJSON.error);
            });
    }

    /**
     * @param {string} val
     * @return {array}
     */
    function split(val) {
        return val.split(/,\s*/);
    }

    /**
     * @param {string} term
     * @returns {string}
     */
    function extractLast(term) {
        return split(term).pop();
    }

    /**
     * hints
     */
    jQuery('.struct .hashint').tooltip();

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
     * Autocomplete for single type
     */
    jQuery('input.struct_autocomplete').autocomplete({
        ismulti: false,
        source: function (request, cb) {
            var name = this.element.attr('name');
            name = name.substring(19, name.length - 1);
            name = name.replace('][', '.');

            var term = request.term;
            if (this.options.ismulti) {
                term = extractLast(term);
            }
            struct_ajax(name, cb, {search: term});
        }
    });

    /**
     * Autocomplete for multi type
     */
    jQuery('.multiwrap input.struct_autocomplete').autocomplete('option', {
        ismulti: true,
        focus: function () {
            // prevent value inserted on focus
            return false;
        },
        select: function (event, ui) {
            var terms = split(this.value);
            // remove the current input
            terms.pop();
            // add the selected item
            terms.push(ui.item.value);
            // add placeholder to get the comma-and-space at the end
            terms.push("");
            this.value = terms.join(", ");
            return false;
        }
    });

    /**
     * Handle tabs in the Schema Editor
     */
    jQuery('#plugin__struct_json').hide();
    jQuery('#plugin__struct_tabs').find('a').click(function (e) {
        e.preventDefault();
        e.stopPropagation();
        var $me = jQuery(this);
        if($me.parent().hasClass('active')) return; // nothing to do

        $me.parent().parent().find('li').removeClass('active');
        $me.parent().addClass('active');
        jQuery('#plugin__struct_json, #plugin__struct_editor').hide();
        jQuery($me.attr('href')).show();
    });


    /**
     * Toggle the disabled class in the schema editor
     */
    jQuery('#plugin__struct_editor').find('td.isenabled input').change(function () {
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
