jQuery(function () {
    /* DOKUWIKI:include script/functions.js */

    /* DOKUWIKI:include script/EntryEditor.js */
    EntryEditor(jQuery('#dw__editform, form.bureaucracy__plugin'));

    /* DOKUWIKI:include script/SchemaEditor.js */
    SchemaEditor();

    /* DOKUWIKI:include script/LookupEditor.js */
    jQuery('div.structlookup table').each(LookupEditor);

    /* DOKUWIKI:include script/InlineEditor.js */
    InlineEditor(jQuery('div.structaggregation table'));
});
