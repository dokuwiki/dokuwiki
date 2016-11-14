/**
 * Lookup Editor
 */
var LookupEditor = function (idx, table) {
    var $table = jQuery(table);
    var $form = null;
    var formdata;

    var schema = $table.parents('.structaggregation').data('schema');
    if (!schema) return;

    /**
     * Adds delete row buttons to each row
     */
    function addDeleteRowButtons() {
        $table.find('tr').each(function () {
            var $me = jQuery(this);

            // already added here?
            if ($me.find('th.action, td.action').length) {
                return;
            }

            // empty header cells
            if ($me.parent().is('thead')) {
                $me.append('<th class="action"></th>');
                return;
            }

            // delete buttons for rows
            var $td = jQuery('<td class="action"></td>');
            var pid = $me.data('pid');
            if (pid === '') return;

            var $btn = jQuery('<button><i class="ui-icon ui-icon-trash"></i></button>')
                .addClass('delete')
                .attr('title', LANG.plugins.struct.lookup_delete)
                .click(function (e) {
                    e.preventDefault();
                    if (!window.confirm(LANG.del_confirm)) return;

                    jQuery.post(
                        DOKU_BASE + 'lib/exe/ajax.php',
                        {
                            call: 'plugin_struct_lookup_delete',
                            schema: schema,
                            pid: pid,
                            sectok: $me.parents('.structlookup').find('.struct_entry_form input[name=sectok]').val()
                        }
                    )
                        .done(function () {
                            $me.remove();
                        })
                        .fail(function (xhr) {
                            alert(xhr.responseText)
                        })
                });

            $td.append($btn);
            $me.append($td);

        });
    }

    /**
     * Initializes the form for the editor and attaches handlers
     *
     * @param {string} data The HTML for the form
     */
    function addForm(data) {
        if ($form) $form.remove();
        var $agg = $table.parents('.structaggregation');

        $form = jQuery('<form></form>');
        $form.html(data);
        jQuery('<input>').attr({
            type: 'hidden',
            name: 'searchconf',
            value: $agg.attr('data-searchconf')
        }).appendTo($form); // add the search config to the form
        $agg.append($form);
        EntryEditor($form);

        var $errors = $form.find('div.err').hide();

        $form.submit(function (e) {
            e.preventDefault();
            $errors.hide();

            jQuery.post(
                DOKU_BASE + 'lib/exe/ajax.php',
                $form.serialize()
            )
                .done(function (data) {
                    const $tbody = $table.find('tbody').length ? $table.find('tbody') : jQuery('<tbody>').appendTo($table);
                    $tbody.append(data);
                    addDeleteRowButtons(); // add the delete button to the new row
                    addForm(formdata); // reset the whole form
                })
                .fail(function (xhr) {
                    $errors.text(xhr.responseText).show();
                })
        });

        // focus first input
        $form.find('input, textarea').first().focus();
    }

    /**
     * Main
     *
     * Initializes the editor if the AJAX backend returns an editor,
     * otherwise some (ACL) check did not check out and no editing
     * capabilites are added.
     */
    jQuery.post(
        DOKU_BASE + 'lib/exe/ajax.php',
        {
            call: 'plugin_struct_lookup_new',
            schema: schema
        },
        function (data) {
            if (!data) return;
            formdata = data;
            addDeleteRowButtons();
            addForm(data);
        }
    );


};
