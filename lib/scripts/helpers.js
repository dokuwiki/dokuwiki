/**
 * Various helper functions
 */

/**
 * A PHP-style substr_replace
 *
 * Supports negative start and length and omitting length, but not
 * str and replace arrays.
 * See http://php.net/substr-replace for further documentation.
 */
function substr_replace(str, replace, start, length) {
    var a2, b1;
    a2 = (start < 0 ? str.length : 0) + start;
    if (typeof length === 'undefined') {
        length = str.length - a2;
    } else if (length < 0 && start < 0 && length <= start) {
        length = 0;
    }
    b1 = (length < 0 ? str.length : a2) + length;
    return str.substring(0, a2) + replace + str.substring(b1);
}

/**
 * Bind variables to a function call creating a closure
 *
 * Use this to circumvent variable scope problems when creating closures
 * inside a loop
 *
 * @author  Adrian Lang <lang@cosmocode.de>
 * @link    http://www.cosmocode.de/en/blog/gohr/2009-10/15-javascript-fixing-the-closure-scope-in-loops
 * @param   functionref fnc - the function to be called
 * @param   mixed - any arguments to be passed to the function
 * @returns functionref
 */
function bind(fnc/*, ... */) {
    var Aps = Array.prototype.slice,
    // Store passed arguments in this scope.
    // Since arguments is no Array nor has an own slice method,
    // we have to apply the slice method from the Array.prototype
        static_args = Aps.call(arguments, 1);

    // Return a function evaluating the passed function with the
    // given args and optional arguments passed on invocation.
    return function (/* ... */) {
        // Same here, but we use Array.prototype.slice solely for
        // converting arguments to an Array.
        return fnc.apply(this,
                         static_args.concat(Aps.call(arguments, 0)));
    };
}

/**
 * Report an error from a JS file to the console
 *
 * @param e    The error object
 * @param file The file in which the error occurred
 */
function logError(e, file) {
    if (window.console && console.error) {
        console.error('The error "%s: %s" occurred in file "%s". ' +
            'If this is in a plugin try updating or disabling the plugin, ' +
            'if this is in a template try updating the template or switching to the "dokuwiki" template.',
            e.name, e.message, file);
    }
}
