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
function bind(fnc/*, ... */) {
    var Aps = Array.prototype.slice;
    // Store passed arguments in this scope.
    // Since arguments is no Array nor has an own slice method,
    // we have to apply the slice method from the Array.prototype
    var static_args = Aps.call(arguments, 1);

    // Return a function evaluating the passed function with the
    // given args and optional arguments passed on invocation.
    return function (/* ... */) {
        // Same here, but we use Array.prototype.slice solely for
        // converting arguments to an Array.
        return fnc.apply(this,
                         static_args.concat(Aps.call(arguments, 0)));
    };
}
