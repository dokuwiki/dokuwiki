/*jslint sloppy: true */
/*global dw_index, dw_qsearch, DEPRECATED_WRAP */

/**
 * Mark a JavaScript function as deprecated
 *
 * This will print a warning to the JavaScript console (if available) in
 * Firebug and Chrome and a stack trace (if available) to easily locate the
 * problematic function call.
 *
 * @param msg optional message to print
 */
function DEPRECATED(msg){
    if(!window.console) return;
    if(!msg) msg = '';

    var func;
    if(arguments.callee) func = arguments.callee.caller.name;
    if(func) func = ' '+func+'()';
    var line = 'DEPRECATED function call'+func+'. '+msg;

    if(console.warn){
        console.warn(line);
    }else{
        console.log(line);
    }

    if(console.trace) console.trace();
}

/**
 * Construct a wrapper function for deprecated function names
 *
 * This function returns a wrapper function which just calls DEPRECATED
 * and the new function.
 *
 * @param func    The new function
 * @param context Optional; The context (`this`) of the call
 */
function DEPRECATED_WRAP(func, context) {
    return function () {
        DEPRECATED();
        return func.apply(context || this, arguments);
    }
}

/**
 * Handy shortcut to document.getElementById
 *
 * This function was taken from the prototype library
 *
 * @link http://prototype.conio.net/
 */
function $() {
    DEPRECATED('Please use the JQuery() function instead.');

    var elements = new Array();

    for (var i = 0; i < arguments.length; i++) {
        var element = arguments[i];
        if (typeof element == 'string')
            element = document.getElementById(element);

        if (arguments.length == 1)
            return element;

        elements.push(element);
    }

    return elements;
}




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

function addEvent(element, type, handler) {
    DEPRECATED('Use jQuery.bind() instead.');
    jQuery(element).bind(type,{},handler);
}

function removeEvent(element, type, handler) {
    DEPRECATED('Use jQuery.unbind() instead.');
    jQuery(element).unbind(type,handler);
}

function addInitEvent(func) {
    DEPRECATED('Use jQuery(<function>) instead');
    jQuery(func);
}


function jsEscape(text){
    DEPRECATED('Insert text through jQuery.text() instead of escaping on your own');
    var re=new RegExp("\\\\","g");
    text=text.replace(re,"\\\\");
    re=new RegExp("'","g");
    text=text.replace(re,"\\'");
    re=new RegExp('"',"g");
    text=text.replace(re,'&quot;');
    re=new RegExp("\\\\\\\\n","g");
    text=text.replace(re,"\\n");
    return text;
}


