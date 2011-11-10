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
    };
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

var locktimer = {
    init: DEPRECATED_WRAP(dw_locktimer.init, dw_locktimer),
    reset: DEPRECATED_WRAP(dw_locktimer.reset, dw_locktimer),
    warning: DEPRECATED_WRAP(dw_locktimer.warning, dw_locktimer),
    clear: DEPRECATED_WRAP(dw_locktimer.clear, dw_locktimer),
    refresh: DEPRECATED_WRAP(dw_locktimer.refresh, dw_locktimer),
    refreshed: DEPRECATED_WRAP(dw_locktimer.refreshed, dw_locktimer)
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
    DEPRECATED('Use jQuery.bind() instead. Note that jQuery’s behaviour' +
               ' when a handler returns false differs from addEvent’s');
    jQuery(element).bind(type,{},function (e) {
        // returning false in an addEvent event handler did not prevent
        // bubbling but just canceled handlers on this node and prevented
        // default behavior, so wrap the handler call and mimic that behavior.
        //
        // Refer to jQuery.event.handle().
        var ret = handler.apply(this, Array.prototype.slice.call(arguments, 0));
        if (typeof ret !== 'undefined') {
            if ( ret !== false ) {
                return ret;
            }
            // What jQuery does.
            e.result = ret;
            e.preventDefault();
            // Not what jQuery does. This would be: event.stopPropagation();
            // Hack it so that immediate propagation (other event handlers on
            // this element) appears stopped without stopping the actual
            // propagation (bubbling)
            e.isImmediatePropagationStopped = function () { return true; };
        }
    });
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

/**
 * Simple function to check if a global var is defined
 *
 * @author Kae Verens
 * @link http://verens.com/archives/2005/07/25/isset-for-javascript/#comment-2835
 */
function isset(varname){
    DEPRECATED("Use `typeof var !== 'undefined'` instead");
    return(typeof(window[varname])!='undefined');
}

/**
 * Checks if property is undefined
 *
 * @param {Object} prop value to check
 * @return {Boolean} true if matched
 * @scope public
 * @author Ilya Lebedev <ilya@lebedev.net>
 */
function isUndefined (prop /* :Object */) /* :Boolean */ {
    DEPRECATED("Use `typeof var === 'undefined'` instead");
    return (typeof prop == 'undefined');
}

/**
 * Checks if property is function
 *
 * @param {Object} prop value to check
 * @return {Boolean} true if matched
 * @scope public
 * @author Ilya Lebedev <ilya@lebedev.net>
 */
function isFunction (prop /* :Object */) /* :Boolean */ {
    DEPRECATED("Use `typeof var === 'function'` instead");
    return (typeof prop == 'function');
}
/**
 * Checks if property is string
 *
 * @param {Object} prop value to check
 * @return {Boolean} true if matched
 * @scope public
 * @author Ilya Lebedev <ilya@lebedev.net>
 */
function isString (prop /* :Object */) /* :Boolean */ {
    DEPRECATED("Use `typeof var === 'string'` instead");
    return (typeof prop == 'string');
}

/**
 * Checks if property is number
 *
 * @param {Object} prop value to check
 * @return {Boolean} true if matched
 * @scope public
 * @author Ilya Lebedev <ilya@lebedev.net>
 */
function isNumber (prop /* :Object */) /* :Boolean */ {
    DEPRECATED("Use `typeof var === 'number'` instead");
    return (typeof prop == 'number');
}

/**
 * Checks if property is the calculable number
 *
 * @param {Object} prop value to check
 * @return {Boolean} true if matched
 * @scope public
 * @author Ilya Lebedev <ilya@lebedev.net>
 */
function isNumeric (prop /* :Object */) /* :Boolean */ {
    DEPRECATED("Use `typeof var === 'number' && !isNaN(var) && isFinite(var)` instead");
    return isNumber(prop)&&!isNaN(prop)&&isFinite(prop);
}

/**
 * Checks if property is array
 *
 * @param {Object} prop value to check
 * @return {Boolean} true if matched
 * @scope public
 * @author Ilya Lebedev <ilya@lebedev.net>
 */
function isArray (prop /* :Object */) /* :Boolean */ {
    DEPRECATED("Use `var instanceof Array` instead");
    return (prop instanceof Array);
}

/**
 *  Checks if property is regexp
 *
 * @param {Object} prop value to check
 * @return {Boolean} true if matched
 * @scope public
 * @author Ilya Lebedev <ilya@lebedev.net>
 */
function isRegExp (prop /* :Object */) /* :Boolean */ {
    DEPRECATED("Use `var instanceof RegExp` instead");
    return (prop instanceof RegExp);
}

/**
 * Checks if property is a boolean value
 *
 * @param {Object} prop value to check
 * @return {Boolean} true if matched
 * @scope public
 * @author Ilya Lebedev <ilya@lebedev.net>
 */
function isBoolean (prop /* :Object */) /* :Boolean */ {
    DEPRECATED("Use `typeof var === 'boolean'` instead");
    return ('boolean' == typeof prop);
}

/**
 * Checks if property is a scalar value (value that could be used as the hash key)
 *
 * @param {Object} prop value to check
 * @return {Boolean} true if matched
 * @scope public
 * @author Ilya Lebedev <ilya@lebedev.net>
 */
function isScalar (prop /* :Object */) /* :Boolean */ {
    DEPRECATED("Use `typeof var === 'string' || (typeof var === 'number' &&" +
               " !isNaN(var) && isFinite(var))` instead");
    return isNumeric(prop)||isString(prop);
}

/**
 * Checks if property is empty
 *
 * @param {Object} prop value to check
 * @return {Boolean} true if matched
 * @scope public
 * @author Ilya Lebedev <ilya@lebedev.net>
 */
function isEmpty (prop /* :Object */) /* :Boolean */ {
    DEPRECATED();
    var i;
    if (isBoolean(prop)) {
        return false;
    } else if (isRegExp(prop) && new RegExp("").toString() == prop.toString()) {
        return true;
    } else if (isString(prop) || isNumber(prop)) {
        return !prop;
    } else if (Boolean(prop) && false != prop) {
        for (i in prop) {
            if(prop.hasOwnProperty(i)) {
                return false;
            }
        }
    }
    return true;
}

/**
 * Get the computed style of a node.
 *
 * @link https://acidmartin.wordpress.com/2008/08/26/style-get-any-css-property-value-of-an-object/
 * @link http://svn.dojotoolkit.org/src/dojo/trunk/_base/html.js
 */
function gcs(node){
    DEPRECATED('Use jQuery(node).style() instead');
    if(node.currentStyle){
        return node.currentStyle;
    }else{
        return node.ownerDocument.defaultView.getComputedStyle(node, null);
    }
}

/**
 * Until 2011-05-25 "Rincewind", a code intended to fix some Safari issue
 * always declared the global _timer. plugin:sortablejs relies on _timer
 * being declared.
 */
var _timer;
