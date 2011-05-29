/**
 * Differrent helper functions
 *
 * @author Ilya Lebedev <ilya@lebedev.net>
 * @license LGPL
 */
//-----------------------------------------------------------------------------
//  Variable/property checks
//-----------------------------------------------------------------------------
/**
 *  Checks if property is undefined
 *
 *  @param {Object} prop value to check
 *  @return {Boolean} true if matched
 *  @scope public
 */
function isUndefined (prop /* :Object */) /* :Boolean */ {
  return (typeof prop == 'undefined');
}
/**
 *  Checks if property is function
 *
 *  @param {Object} prop value to check
 *  @return {Boolean} true if matched
 *  @scope public
 */
function isFunction (prop /* :Object */) /* :Boolean */ {
  return (typeof prop == 'function');
}
/**
 *  Checks if property is string
 *
 *  @param {Object} prop value to check
 *  @return {Boolean} true if matched
 *  @scope public
 */
function isString (prop /* :Object */) /* :Boolean */ {
  return (typeof prop == 'string');
}
/**
 *  Checks if property is number
 *
 *  @param {Object} prop value to check
 *  @return {Boolean} true if matched
 *  @scope public
 */
function isNumber (prop /* :Object */) /* :Boolean */ {
  return (typeof prop == 'number');
}
/**
 *  Checks if property is the calculable number
 *
 *  @param {Object} prop value to check
 *  @return {Boolean} true if matched
 *  @scope public
 */
function isNumeric (prop /* :Object */) /* :Boolean */ {
  return isNumber(prop)&&!isNaN(prop)&&isFinite(prop);
}
/**
 *  Checks if property is array
 *
 *  @param {Object} prop value to check
 *  @return {Boolean} true if matched
 *  @scope public
 */
function isArray (prop /* :Object */) /* :Boolean */ {
  return (prop instanceof Array);
}
/**
 *  Checks if property is regexp
 *
 *  @param {Object} prop value to check
 *  @return {Boolean} true if matched
 *  @scope public
 */
function isRegExp (prop /* :Object */) /* :Boolean */ {
  return (prop instanceof RegExp);
}
/**
 *  Checks if property is a boolean value
 *
 *  @param {Object} prop value to check
 *  @return {Boolean} true if matched
 *  @scope public
 */
function isBoolean (prop /* :Object */) /* :Boolean */ {
  return ('boolean' == typeof prop);
}
/**
 *  Checks if property is a scalar value (value that could be used as the hash key)
 *
 *  @param {Object} prop value to check
 *  @return {Boolean} true if matched
 *  @scope public
 */
function isScalar (prop /* :Object */) /* :Boolean */ {
  return isNumeric(prop)||isString(prop);
}
/**
 *  Checks if property is empty
 *
 *  @param {Object} prop value to check
 *  @return {Boolean} true if matched
 *  @scope public
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
 *  Checks if property is derived from prototype, applies method if it is not exists
 *
 *  @param string property name
 *  @return bool true if prototyped
 *  @access public
 */
if ('undefined' == typeof Object.hasOwnProperty) {
  Object.prototype.hasOwnProperty = function (prop) {
    return !('undefined' == typeof this[prop] || this.constructor && this.constructor.prototype[prop] && this[prop] === this.constructor.prototype[prop]);
  };
}

/**
 * Very simplistic Flash plugin check, probably works for Flash 8 and higher only
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
