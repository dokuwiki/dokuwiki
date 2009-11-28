/**
 * Makes a DOM object draggable
 *
 * This is currently for movable DOM dialogs only. If needed it could be
 * extended to execute callbacks on special events...
 *
 * @link http://nofunc.org/Drag_Drop/
 */
var drag = {
    obj: null,
    handle: null,
    oX: 0,  // object X position
    oY: 0,  // object Y position
    eX: 0,  // event X delta
    eY: 0,  // event Y delta

    /**
     * Attaches the needed handlers to the given object
     *
     * This can be called for multiple objects, the right one is later
     * determined from the event itself. The handle is optional
     *
     * @param DOMObject obj    The object that should be draggable
     * @param DOMObject handle A handle on which the obj can be dragged
     */
    attach: function (obj,handle) {
        if(handle){
            handle.dragobject = obj;
            addEvent($(handle),'mousedown',drag.start);
        }else{
            addEvent($(obj),'mousedown',drag.start);
        }
    },

    /**
     * Starts the dragging operation
     */
    start: function (e){
        drag.handle = e.target;
        if(drag.handle.dragobject){
            drag.obj = drag.handle.dragobject;
        }else{
            drag.obj = drag.handle;
        }

        drag.handle.className += ' ondrag';
        drag.obj.className    += ' ondrag';

        drag.oX = parseInt(drag.obj.style.left);
        drag.oY = parseInt(drag.obj.style.top);
        drag.eX = drag.evX(e);
        drag.eY = drag.evY(e);

        addEvent(document,'mousemove',drag.drag);
        addEvent(document,'mouseup',drag.stop);

        e.preventDefault();
        e.stopPropagation();
        return false;
    },

    /**
     * Ends the dragging operation
     */
    stop: function(){
        drag.handle.className = drag.handle.className.replace(/ ?ondrag/,'');
        drag.obj.className    = drag.obj.className.replace(/ ?ondrag/,'');
        removeEvent(document,'mousemove',drag.drag);
        removeEvent(document,'mouseup',drag.stop);
        drag.obj = null;
        drag.handle = null;
    },

    /**
     * Moves the object during the dragging operation
     */
    drag: function(e) {
        if(drag.obj) {
            drag.obj.style.top  = (drag.evY(e)+drag.oY-drag.eY+'px');
            drag.obj.style.left = (drag.evX(e)+drag.oX-drag.eX+'px');
        }
    },

    /**
     * Returns the X position of the given event.
     */
    evX: function(e){
        return (e.pageX) ? e.pageX : e.clientX + document.body.scrollTop; //fixme shouldn't this be scrollLeft?
    },

    /**
     * Returns the Y position of the given event.
     */
    evY: function(e){
        return (e.pageY) ? e.pageY : e.clientY + document.body.scrollTop;
    }

};

