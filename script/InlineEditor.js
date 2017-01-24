/**
 * Inline Editor
 */
var InlineEditor = function ($table) {


    $table.on('dblclick', 'td', function (e) {
        e.preventDefault();
        e.stopPropagation();

        var $self = jQuery(this);
        var pid = $self.parent().data('pid');
        var field = $self.parents('table').find('tr th').eq($self.index()).data('field');

        if (!pid) return;
        if (!field) return;

        // prepare the edit overlay
        var $div = jQuery('<div class="struct_inlineditor"><form></form><div class="err"></div></div>');
        var $form = $div.find('form');
        var $errors = $div.find('div.err').hide();
        var $save = jQuery('<button type="submit">Save</button>');
        var $cancel = jQuery('<button>Cancel</button>');
        $form.append(jQuery('<input type="hidden" name="pid">').val(pid));
        $form.append(jQuery('<input type="hidden" name="field">').val(field));
        $form.append('<input type="hidden" name="call" value="plugin_struct_inline_save">');
        $form.append(jQuery('<div class="ctl">').append($save).append($cancel));

        /**
         * load the editor
         */
        jQuery.post(
            DOKU_BASE + 'lib/exe/ajax.php',
            {
                call: 'plugin_struct_inline_editor',
                pid: pid,
                field: field
            },
            function (data) {
                if (!data) return; // we're done

                $form.prepend(data);

                // show
                $self.closest('.dokuwiki').append($div);
                $div.position({
                    my: 'left top',
                    at: 'left top',
                    of: $self
                });

                // attach entry handlers to the inline form
                EntryEditor($form);

                // focus first input
                $form.find('input, textarea').first().focus();
            }
        );

        /**
         * Save the data, then close the form
         */
        $form.submit(function (e) {
            e.preventDefault();
            jQuery.post(
                DOKU_BASE + 'lib/exe/ajax.php',
                $form.serialize()
            )
                .done(function (data) {
                    // save succeeded display new value and close editor
                    $self.html(data);
                    $div.remove();
                    // sums are now out of date - remove them til page is reloaded
                    $self.parents('table').find('tr.summarize').remove();
                })
                .fail(function (data) {
                    // something went wrong, display error
                    $errors.text(data.responseText).show();
                })
            ;


        });

        /**
         * Close the editor without saving
         */
        $cancel.click(function (e) {
            // unlock page
            jQuery.post(
                DOKU_BASE + 'lib/exe/ajax.php',
                {
                    call: 'plugin_struct_inline_cancel',
                    pid: pid
                }
            );

            e.preventDefault();
            $div.remove();
        });
    });

};
