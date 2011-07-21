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

var media_manager = {
    // treeattach, selectorattach, confirmattach are munched together into
    // dw_mediamanager.init
    attachoptions: DEPRECATED_WRAP(dw_mediamanager.attachoptions, dw_mediamanager),
    togglekeepopen: function (event, cb) {
        DEPRECATED('Use dw_mediamanager.toggleOption instead');
        return dw_mediamanager.toggleOption.call(cb, 'keepopen');
    },
    togglehide: function (event, cb) {
        DEPRECATED('Use dw_mediamanager.toggleOption instead');
        return dw_mediamanager.toggleOption.call(cb, 'hide');
    },
    updatehide: DEPRECATED_WRAP(dw_mediamanager.updatehide, dw_mediamanager),
    select: function (event, link) {
        DEPRECATED('Use dw_mediamanager.select instead');
        return dw_mediamanager.select.call(link, event);
    },
    initpopup: DEPRECATED_WRAP(dw_mediamanager.initpopup, dw_mediamanager),
    insert: DEPRECATED_WRAP(dw_mediamanager.insert, dw_mediamanager),
    list: function (event, link) {
        DEPRECATED('Use dw_mediamanager.list instead');
        return dw_mediamanager.list.call(link, event);
    },
    // toggle is handled by dw_tree
    suggest: DEPRECATED_WRAP(dw_mediamanager.suggest, dw_mediamanager),
    initFlashUpload: DEPRECATED_WRAP(dw_mediamanager.initFlashUpload, dw_mediamanager),
    closePopup: function () {
        DEPRECATED();
        dw_mediamanager.$popup.dialog('close');
    },
    setalign: function (event, cb) {
        DEPRECATED('Use dw_mediamanager.setOpt instead');
        return dw_mediamanager.setOpt.call(this, 'align', event);
    },
    setlink: function (event, cb) {
        DEPRECATED('Use dw_mediamanager.setOpt instead');
        return dw_mediamanager.setOpt.call(this, 'link', event);
    },
    setsize: function (event, cb) {
        DEPRECATED('Use dw_mediamanager.setOpt instead');
        return dw_mediamanager.setOpt.call(this, 'size', event);
    },
    outSet: function (id) {
        DEPRECATED();
        return jQuery(id).removeClass('selected');
    },
    inSet: function (id) {
        DEPRECATED();
        return jQuery(id).addClass('selected');
    }
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
    if(typeof tag === 'undefined') tag = '';
    return jQuery(node).find(tag+'.'+searchClass).toArray();
}

function prependChild(parent,element) {
    DEPRECATED('Use jQuery.prepend() instead');
    jQuery(parent).prepend(element);
}

