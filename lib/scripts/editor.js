/**
 * The DokuWiki editor features
 *
 * These are the advanced features of the editor. It does NOT contain any
 * code for the toolbar buttons and it functions. See toolbar.js for that.
 */

var dw_editor = {

    /**
     * initialize the default editor functionality
     *
     * All other functions can also be called separately for non-default
     * textareas
     */
    init: function(){
        var $editor = jQuery('#wiki__text');
        if($editor.length === 0) {
            return;
        }

        dw_editor.initSizeCtl('#size__ctl',$editor);

        if($editor.attr('readOnly')) {
            return;
        }

        $editor.keydown(dw_editor.keyHandler);

    },

    /**
     * Add the edit window size and wrap controls
     *
     * Initial values are read from cookie if it exists
     *
     * @param selector ctlarea the div to place the controls
     * @param selector editor  the textarea to control
     */
    initSizeCtl: function(ctlarea,editor){
        var $ctl      = jQuery(ctlarea),
            $textarea = jQuery(editor);

        if($ctl.length === 0 || $textarea.length === 0) {
            return;
        }

        $textarea.css('height', DokuCookie.getValue('sizeCtl') || '300px');

        var wrp = DokuCookie.getValue('wrapCtl');
        if(wrp){
            dw_editor.setWrap($textarea[0], wrp);
        } // else use default value

        jQuery.each([
            ['larger', function(){dw_editor.sizeCtl(editor,100);}],
            ['smaller', function(){dw_editor.sizeCtl(editor,-100);}],
            ['wrap', function(){dw_editor.toggleWrap(editor);}]
        ], function (_, img) {
            jQuery(document.createElement('img'))
                .attr('src', DOKU_BASE+'lib/images/' + img[0] + '.gif')
                .attr('alt', '')
                .on('click', img[1])
                .appendTo($ctl);
        });
    },

    /**
     * This sets the vertical size of the editbox and adjusts the cookie
     *
     * @param selector editor  the textarea to control
     * @param int val          the relative value to resize in pixel
     */
    sizeCtl: function(editor,val){
        var $textarea = jQuery(editor),
            height = parseInt($textarea.css('height')) + val;
        $textarea.css('height', height+'px');
        DokuCookie.setValue('sizeCtl',$textarea.css('height'));
    },

    /**
     * Toggle the wrapping mode of the editor textarea and adjusts the
     * cookie
     *
     * @param selector editor  the textarea to control
     */
    toggleWrap: function(editor){
        var $textarea = jQuery(editor),
            wrap = $textarea.attr('wrap');
        dw_editor.setWrap($textarea[0],
                          (wrap && wrap.toLowerCase() == 'off') ? 'soft' : 'off');
        DokuCookie.setValue('wrapCtl',$textarea.attr('wrap'));
    },

    /**
     * Set the wrapping mode of a textarea
     *
     * @author Fluffy Convict <fluffyconvict@hotmail.com>
     * @author <shutdown@flashmail.com>
     * @link   http://news.hping.org/comp.lang.javascript.archive/12265.html
     * @link   https://bugzilla.mozilla.org/show_bug.cgi?id=41464
     * @param  DomObject textarea
     * @param  string wrapAttrValue
     */
    setWrap: function(textarea, wrapAttrValue){
        textarea.setAttribute('wrap', wrapAttrValue);

        // Fix display for mozilla
        var parNod = textarea.parentNode;
        var nxtSib = textarea.nextSibling;
        parNod.removeChild(textarea);
        parNod.insertBefore(textarea, nxtSib);
    },

    /**
     * Make intended formattings easier to handle
     *
     * Listens to all key inputs and handle indentions
     * of lists and code blocks
     *
     * Currently handles space, backspace, enter and
     * ctrl-enter presses
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     * @fixme handle tabs
     * @param event e - the key press event object
     */
    keyHandler: function(e){
        if(jQuery.inArray(e.keyCode,[8, 10, 13, 32]) === -1) {
            return;
        }
        var selection = DWgetSelection(this);
        if(selection.getLength() > 0) {
            return; //there was text selected, keep standard behavior
        }
        var search    = "\n"+this.value.substr(0,selection.start);
        var linestart = Math.max(search.lastIndexOf("\n"),
                                 search.lastIndexOf("\r")); //IE workaround
        search = search.substr(linestart);

        if((e.keyCode == 13 || e.keyCode == 10) && e.ctrlKey) { // Ctrl-Enter (With Chrome workaround)
            // Submit current edit
            jQuery('#edbtn__save').trigger('click');
            e.preventDefault(); // prevent enter key
            return false;
        }else if(e.keyCode == 13){ // Enter
            // keep current indention for lists and code
            var match = search.match(/(\n  +([\*-] ?)?)/);
            if(match){
                var scroll = this.scrollHeight;
                var match2 = search.match(/^\n  +[\*-]\s*$/);
                // Cancel list if the last item is empty (i. e. two times enter)
                if (match2 && this.value.substr(selection.start).match(/^($|\r?\n)/)) {
                    this.value = this.value.substr(0, linestart) + "\n" +
                                 this.value.substr(selection.start);
                    selection.start = linestart + 1;
                    selection.end = linestart + 1;
                    DWsetSelection(selection);
                } else {
                    insertAtCarret(this.id,match[1]);
                }
                this.scrollTop += (this.scrollHeight - scroll);
                e.preventDefault(); // prevent enter key
                return false;
            }
        }else if(e.keyCode == 8){ // Backspace
            // unindent lists
            var match = search.match(/(\n  +)([*-] ?)$/);
            if(match){
                var spaces = match[1].length-1;

                if(spaces > 3){ // unindent one level
                    this.value = this.value.substr(0,linestart)+
                                 this.value.substr(linestart+2);
                    selection.start = selection.start - 2;
                    selection.end   = selection.start;
                }else{ // delete list point
                    this.value = this.value.substr(0,linestart)+
                                 this.value.substr(selection.start);
                    selection.start = linestart;
                    selection.end   = linestart;
                }
                DWsetSelection(selection);
                e.preventDefault(); // prevent backspace
                return false;
            }
        }else if(e.keyCode == 32){ // Space
            // intend list item
            var match = search.match(/(\n  +)([*-] )$/);
            if(match){
                this.value = this.value.substr(0,linestart)+'  '+
                             this.value.substr(linestart);
                selection.start = selection.start + 2;
                selection.end   = selection.start;
                DWsetSelection(selection);
                e.preventDefault(); // prevent space
                return false;
            }
        }
    }


};

jQuery(dw_editor.init);
