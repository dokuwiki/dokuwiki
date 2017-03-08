/**
 * Attaches all the special handlers to the entry edit form
 *
 * @param {jQuery} $form The form where all the handlers are attached
 */
var EntryEditor = function($form) {

    /** counter for copied multi templates */
    var copycount = 0;

    /**
     * hints
     */
    $form.find('.struct .hashint').tooltip();

    /**
     * Attach datepicker to date types
     */
    $form.find('input.struct_date').datepicker({
        dateFormat: 'yy-mm-dd'
    });

    /**
     * Attach datepicker to datetype types, keeps time part
     */
    $form.find('input.struct_datetime').datepicker({
        dateFormat: 'yy-mm-dd',
        onSelect: function (date, inst) {
            var $input = jQuery(this);
            var both = inst.lastVal.split(' ', 2);
            if (both.length == 2) {
                date += ' ' + both[1];
            } else {
                date += ' 00:00:00';
            }
            $input.val(date);
        }
    });

    /**
     * Attach image dialog to image types
     */
    $form.find('button.struct_media').click(function () {
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
     * Autocomplete for single type
     */
    $form.find('input.struct_autocomplete').autocomplete({
        ismulti: false,
        source: function (request, cb) {
            var name = jQuery(this.element[0]).closest('label').data('column');
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
    $form.find('.multiwrap input.struct_autocomplete').autocomplete('option', {
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
     * Duplicate the elements in .newtemplate whenever any input in it changes
     */
    $form.find('.newtemplate').each(function () {
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

            // move edited .multiwrap out of .newtemplate container
            $tplwrapper.before(jQuery(this).closest('.multiwrap').detach());

            // append the template
            $tplwrapper.append($copy);
        });
    });

    /**
     * Toggle fieldsets in edit form and remeber in cookie
     */
    $form.find('.struct_entry_form fieldset legend').each(function () {
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

};
