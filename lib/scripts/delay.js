/**
 * Manage delayed and timed actions
 *
 * @license GPL2 (http://www.gnu.org/licenses/gpl.html)
 * @author  Adrian Lang <lang@cosmocode.de>
 */

/**
 * Provide a global callback for window.setTimeout
 *
 * To get a timeout for non-global functions, just call
 * delay.add(func, timeout).
 */
var timer = {
    _cur_id: 0,
    _handlers: {},

    execDispatch: function (id) {
        timer._handlers[id]();
    },

    add: function (func, timeout) {
        var id = ++timer._cur_id;
        timer._handlers[id] = func;
        return window.setTimeout('timer.execDispatch(' + id + ')', timeout);
    }
};

/**
 * Provide a delayed start
 *
 * To call a function with a delay, just create a new Delay(func, timeout) and
 * call that object’s method “start”.
 */
function Delay (func, timeout) {
    this.func = func;
    if (timeout) {
        this.timeout = timeout;
    }
}

Delay.prototype = {
    func: null,
    timeout: 500,

    delTimer: function () {
        if (this.timer !== null) {
            window.clearTimeout(this.timer);
            this.timer = null;
        }
    },

    start: function () {
        DEPRECATED('don\'t use the Delay object, use window.timeout with a callback instead');
        this.delTimer();
        var _this = this;
        this.timer = timer.add(function () { _this.exec.call(_this); },
                               this.timeout);

        this._data = {
            _this: arguments[0],
            _params: Array.prototype.slice.call(arguments, 2)
        };
    },

    exec: function () {
        this.delTimer();
        this.func.call(this._data._this, this._data._params);
    }
};
