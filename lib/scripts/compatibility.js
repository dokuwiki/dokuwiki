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
