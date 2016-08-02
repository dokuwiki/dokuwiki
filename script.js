jQuery(function () {
    /* DOKUWIKI:include script/functions.js */

    /* DOKUWIKI:include script/EntryEditor.js */
    EntryEditor(jQuery('#dw__editform'));

    /* DOKUWIKI:include script/SchemaEditor.js */
    SchemaEditor();

    /* DOKUWIKI:include script/InlineEditor.js */
    InlineEditor(jQuery('div.structaggregation table'));
});
