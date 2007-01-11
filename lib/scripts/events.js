// written by Dean Edwards, 2005
// with input from Tino Zijdel

// http://dean.edwards.name/weblog/2005/10/add-event/

function addEvent(element, type, handler) {
    // assign each event handler a unique ID
    if (!handler.$$guid) handler.$$guid = addEvent.guid++;
    // create a hash table of event types for the element
    if (!element.events) element.events = {};
    // create a hash table of event handlers for each element/event pair
    var handlers = element.events[type];
    if (!handlers) {
        handlers = element.events[type] = {};
        // store the existing event handler (if there is one)
        if (element["on" + type]) {
            handlers[0] = element["on" + type];
        }
    }
    // store the event handler in the hash table
    handlers[handler.$$guid] = handler;
    // assign a global event handler to do all the work
    element["on" + type] = handleEvent;
};
// a counter used to create unique IDs
addEvent.guid = 1;

function removeEvent(element, type, handler) {
    // delete the event handler from the hash table
    if (element.events && element.events[type]) {
        delete element.events[type][handler.$$guid];
    }
};

function handleEvent(event) {
    var returnValue = true;
    // grab the event object (IE uses a global event object)
    event = event || fixEvent(window.event);
    // get a reference to the hash table of event handlers
    var handlers = this.events[event.type];
    // execute each event handler
    for (var i in handlers) {
        if (!handlers.hasOwnProperty(i)) continue;
        this.$$handleEvent = handlers[i];
        if (this.$$handleEvent(event) === false) {
            returnValue = false;
        }
    }
    return returnValue;
};

function fixEvent(event) {
    // add W3C standard event methods
    event.preventDefault = fixEvent.preventDefault;
    event.stopPropagation = fixEvent.stopPropagation;
    // fix target
    event.target = event.srcElement;
    return event;
};
fixEvent.preventDefault = function() {
    this.returnValue = false;
};
fixEvent.stopPropagation = function() {
    this.cancelBubble = true;
};


/**
 * Pseudo event handler to be fired after the DOM was parsed or
 * on window load at last.
 *
 * @author based upon some code by Dean Edwards
 * @author Dean Edwards
 * @link   http://dean.edwards.name/weblog/2006/06/again/
 */
window.fireoninit = function() {
  // quit if this function has already been called
  if (arguments.callee.done) return;
  // flag this function so we don't do the same thing twice
  arguments.callee.done = true;
  // kill the timer
  if (_timer) {
     clearInterval(_timer);
     _timer = null;
  }

  if (typeof window.oninit == 'function') {
        window.oninit();
  }
};

/**
 * Run the fireoninit function as soon as possible after
 * the DOM was loaded, using different methods for different
 * Browsers
 *
 * @author Dean Edwards
 * @link   http://dean.edwards.name/weblog/2006/06/again/
 */
  // for Mozilla
  if (document.addEventListener) {
    document.addEventListener("DOMContentLoaded", window.fireoninit, null);
  }

  // for Internet Explorer (using conditional comments)
  /*@cc_on @*/
  /*@if (@_win32)
    document.write("<scr" + "ipt id=\"__ie_init\" defer=\"true\" src=\"//:\"><\/script>");
    var script = document.getElementById("__ie_init");
    script.onreadystatechange = function() {
        if (this.readyState == "complete") {
            window.fireoninit(); // call the onload handler
        }
    };
  /*@end @*/

  // for Safari
  if (/WebKit/i.test(navigator.userAgent)) { // sniff
    var _timer = setInterval(function() {
        if (/loaded|complete/.test(document.readyState)) {
            window.fireoninit(); // call the onload handler
        }
    }, 10);
  }

  // for other browsers
  window.onload = window.fireoninit;


/**
 * This is a pseudo Event that will be fired by the fireoninit
 * function above.
 *
 * Use addInitEvent to bind to this event!
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @see fireoninit()
 */
window.oninit = function(){};

/**
 * Bind a function to the window.init pseudo event
 *
 * @author Simon Willison
 * @see http://simon.incutio.com/archive/2004/05/26/addLoadEvent
 */
function addInitEvent(func) {
  var oldoninit = window.oninit;
  if (typeof window.oninit != 'function') {
    window.oninit = func;
  } else {
    window.oninit = function() {
      oldoninit();
      func();
    };
  }
}


