/**
 * Various helper functions
 */


/**
 * Simple function to check if a global var is defined
 *
 * @author Kae Verens
 * @link http://verens.com/archives/2005/07/25/isset-for-javascript/#comment-2835
 */
function isset(varname){
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
    if (isBoolean(prop)) return false;
    if (isRegExp(prop) && new RegExp("").toString() == prop.toString()) return true;
    if (isString(prop) || isNumber(prop)) return !prop;
    if (Boolean(prop)&&false != prop) {
        for (var i in prop) if(prop.hasOwnProperty(i)) return false;
    }
    return true;
}

/**
 * Checks if property is derived from prototype, applies method if it is not exists
 *
 * @param string property name
 * @return bool true if prototyped
 * @access public
 * @author Ilya Lebedev <ilya@lebedev.net>
 */
if ('undefined' == typeof Object.hasOwnProperty) {
    Object.prototype.hasOwnProperty = function (prop) {
       return !('undefined' == typeof this[prop] || this.constructor && this.constructor.prototype[prop] && this[prop] === this.constructor.prototype[prop]);
    };
}

/**
 * Very simplistic Flash plugin check, probably works for Flash 8 and higher only
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function hasFlash(version){
    var ver = 0;
    try{
        if(navigator.plugins != null && navigator.plugins.length > 0){
           ver = navigator.plugins["Shockwave Flash"].description.split(' ')[2].split('.')[0];
        }else{
           var axo = new ActiveXObject("ShockwaveFlash.ShockwaveFlash");
           ver = axo.GetVariable("$version").split(' ')[1].split(',')[0];
        }
    }catch(e){ }

    if(ver >= version) return true;
    return false;
}

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

/**
 * Get the computed style of a node.
 *
 * @link https://acidmartin.wordpress.com/2008/08/26/style-get-any-css-property-value-of-an-object/
 * @link http://svn.dojotoolkit.org/src/dojo/trunk/_base/html.js
 */
function gcs(node){
    if(node.currentStyle){
        return node.currentStyle;
    }else{
        return node.ownerDocument.defaultView.getComputedStyle(node, null);
    }
}

