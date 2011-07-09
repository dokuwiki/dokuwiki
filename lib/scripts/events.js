/**
 * The event functions are no longer in use and a mere wrapper around
 * jQuery's event handlers.
 *
 * @deprecated
 */
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

/**
 * Bind variables to a function call creating a closure
 *
 * Use this to circumvent variable scope problems when creating closures
 * inside a loop
 *
 * @author  Adrian Lang <lang@cosmocode.de>
 * @fixme   Is there a jQuery equivalent? Otherwise move to somewhere else
 * @link    http://www.cosmocode.de/en/blog/gohr/2009-10/15-javascript-fixing-the-closure-scope-in-loops
 * @param   functionref fnc - the function to be called
 * @param   mixed - any arguments to be passed to the function
 * @returns functionref
 */
function bind (fnc) {
    var args = Array.prototype.slice.call(arguments, 1);
    return function() {
        return fnc.apply(this, args);
    };
}
