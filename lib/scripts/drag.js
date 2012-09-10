/**
 * Makes a DOM object draggable
 *
 * If you just want to move objects around, use drag.attach. For full
 * customization, drag can be used as a javascript prototype, it is
 * inheritance-aware.
 *
 * @deprecated
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
        DEPRECATED('Use jQuery.draggable() instead.');
        if(handle){
            handle.dragobject = obj;
        }else{
            handle = obj;
        }
        var _this = this;
        addEvent($(handle),'mousedown',function (e) {return _this.start(e); });
    },

    /**
     * Starts the dragging operation
     */
    start: function (e){
        this.handle = e.target;
        if(this.handle.dragobject){
            this.obj = this.handle.dragobject;
        }else{
            this.obj = this.handle;
        }

        this.handle.className += ' ondrag';
        this.obj.className    += ' ondrag';

        this.oX = parseInt(this.obj.style.left);
        this.oY = parseInt(this.obj.style.top);
        this.eX = e.pageX;
        this.eY = e.pageY;

        var _this = this;
        this.mousehandlers = [function (e) {return _this.drag(e);}, function (e) {return _this.stop(e);}];
        addEvent(document,'mousemove', this.mousehandlers[0]);
        addEvent(document,'mouseup', this.mousehandlers[1]);

        return false;
    },

    /**
     * Ends the dragging operation
     */
    stop: function(){
        this.handle.className = this.handle.className.replace(/ ?ondrag/,'');
        this.obj.className    = this.obj.className.replace(/ ?ondrag/,'');
        removeEvent(document,'mousemove', this.mousehandlers[0]);
        removeEvent(document,'mouseup', this.mousehandlers[1]);
        this.obj = null;
        this.handle = null;
    },

    /**
     * Moves the object during the dragging operation
     */
    drag: function(e) {
        if(this.obj) {
            this.obj.style.top  = (e.pageY+this.oY-this.eY+'px');
            this.obj.style.left = (e.pageX+this.oX-this.eX+'px');
        }
    }
};
