/*jslint sloppy: true */
/*global dw_index, dw_qsearch, DEPRECATED_WRAP */

var index = {
    throbber_delay: dw_index.throbber_delay,
    toggle: DEPRECATED_WRAP(dw_index.toggle, dw_index),
    treeattach: DEPRECATED_WRAP(dw_index.treeattach, dw_index)
};

var ajax_quicksearch = {
    init: DEPRECATED_WRAP(dw_qsearch.init, dw_qsearch),
    clear_results: DEPRECATED_WRAP(dw_qsearch.clear_results, dw_qsearch),
    onCompletion: DEPRECATED_WRAP(dw_qsearch.onCompletion, dw_qsearch)
};

var linkwiz = {
    init: DEPRECATED_WRAP(dw_linkwiz.init, dw_linkwiz),
    onEntry: DEPRECATED_WRAP(dw_linkwiz.onEntry, dw_linkwiz),
    getResult: DEPRECATED_WRAP(dw_linkwiz.getResult, dw_linkwiz),
    select: DEPRECATED_WRAP(dw_linkwiz.select, dw_linkwiz),
    deselect: DEPRECATED_WRAP(dw_linkwiz.deselect, dw_linkwiz),
    onResultClick: DEPRECATED_WRAP(dw_linkwiz.onResultClick, dw_linkwiz),
    resultClick: DEPRECATED_WRAP(dw_linkwiz.resultClick, dw_linkwiz),
    insertLink: DEPRECATED_WRAP(dw_linkwiz.insertLink, dw_linkwiz),
    autocomplete: DEPRECATED_WRAP(dw_linkwiz.autocomplete, dw_linkwiz),
    autocomplete_exec: DEPRECATED_WRAP(dw_linkwiz.autocomplete_exec, dw_linkwiz),
    show: DEPRECATED_WRAP(dw_linkwiz.show, dw_linkwiz),
    hide: DEPRECATED_WRAP(dw_linkwiz.hide, dw_linkwiz),
    toggle: DEPRECATED_WRAP(dw_linkwiz.toggle, dw_linkwiz)
};

initSizeCtl = DEPRECATED_WRAP(dw_editor.initSizeCtl);
sizeCtl = DEPRECATED_WRAP(dw_editor.sizeCtl);
toggleWrap = DEPRECATED_WRAP(dw_editor.toggleWrap);
setWrap = DEPRECATED_WRAP(dw_editor.setWrap);

function findPosX(object){
    DEPRECATED('Use jQuery.position() instead');
    return jQuery(object).position().left;
}

function findPosY(object){
    DEPRECATED('Use jQuery.position() instead');
    return jQuery(object).position().top;
}

function getElementsByClass(searchClass,node,tag){
    DEPRECATED('Use jQuery() instead');
    if(node == null) node = document;
    return jQuery(node).find(tag+'.'+searchClass).toArray();
}

function prependChild(parent,element) {
    DEPRECATED('Use jQuery.prepend() instead');
    jQuery(parent).prepend(element);
}

