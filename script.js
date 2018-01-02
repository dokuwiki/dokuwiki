jQuery(function() {
    /* DOKUWIKI:include script/functions.js */
    /* DOKUWIKI:include script/EntryEditor.js */
    /* DOKUWIKI:include script/SchemaEditor.js */
    /* DOKUWIKI:include script/LookupEditor.js */
    /* DOKUWIKI:include script/InlineEditor.js */

    function init() {
        EntryEditor(jQuery('#dw__editform, form.bureaucracy__plugin'));
        SchemaEditor();
        jQuery('div.structlookup table').each(LookupEditor);
        InlineEditor(jQuery('div.structaggregation table'));
    }

    jQuery(init);

    jQuery(window).on('fastwiki:afterSwitch', function(evt, viewMode, isSectionEdit, prevViewMode) {
        if (viewMode=="edit" || isSectionEdit) {
            EntryEditor(jQuery('#dw__editform, form.bureaucracy__plugin'));
        }
    });
});
