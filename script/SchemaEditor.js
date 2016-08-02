var SchemaEditor = function () {
    /**
     * Handle tabs in the Schema Editor
     */
    jQuery('#plugin__struct_json, #plugin__struct_delete').hide();
    jQuery('#plugin__struct_tabs').find('a').click(function (e) {
        e.preventDefault();
        e.stopPropagation();
        var $me = jQuery(this);
        if ($me.parent().hasClass('active')) return; // nothing to do

        $me.parent().parent().find('li').removeClass('active');
        $me.parent().addClass('active');
        jQuery('#plugin__struct_json, #plugin__struct_editor, #plugin__struct_delete').hide();
        jQuery($me.attr('href')).show();
    });


    /**
     * Toggle the disabled class in the schema editor
     */
    jQuery('#plugin__struct_editor').find('td.isenabled input').change(function () {
        var $checkbox = jQuery(this);
        $checkbox.parents('tr').toggleClass('disabled', !$checkbox.prop('checked'));
    });

    /**
     * Confirm Schema Deletion
     */
    jQuery('a.deleteSchema').click(function (event) {
        var schema = jQuery(this).closest('tr').find('td:nth-child(2)').text();
        var page = jQuery(this).closest('tr').find('td:nth-child(1)').text();
        if (!window.confirm(formatString(LANG.plugins.struct['confirmAssignmentsDelete'], schema, page))) {
            event.preventDefault();
            event.stopPropagation();
        }
    });
};
