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
        if(!$editor.length) return;

        dw_editor.initSizeCtl('#size__ctl',$editor);

        if($editor.attr('readOnly')) return;

        // in Firefox, keypress doesn't send the correct keycodes,
        // in Opera, the default of keydown can't be prevented
        if (jQuery.browser.opera) {
            $editor.keypress(dw_editor.keyHandler);
        } else {
            $editor.keydown(dw_editor.keyHandler);
        }

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
        var $ctl      = jQuery(ctlarea);
        var $textarea = jQuery(editor);
        if(!$ctl.length || !$textarea.length) return;

        var hgt = DokuCookie.getValue('sizeCtl');
        if(hgt){
            $textarea.css('height', hgt);
        }else{
            $textarea.css('height', '300px');
        }

        var wrp = DokuCookie.getValue('wrapCtl');
        if(wrp){
            dw_editor.setWrap($textarea[0], wrp);
        } // else use default value

        var l = document.createElement('img');
        var s = document.createElement('img');
        var w = document.createElement('img');
        l.src = DOKU_BASE+'lib/images/larger.gif';
        s.src = DOKU_BASE+'lib/images/smaller.gif';
        w.src = DOKU_BASE+'lib/images/wrap.gif';
        jQuery(l).click(function(){dw_editor.sizeCtl(editor,100);});
        jQuery(s).click(function(){dw_editor.sizeCtl(editor,-100);});
        jQuery(w).click(function(){dw_editor.toggleWrap(editor);});
        $ctl.append(l);
        $ctl.append(s);
        $ctl.append(w);
    },

    /**
     * This sets the vertical size of the editbox and adjusts the cookie
     *
     * @param selector editor  the textarea to control
     * @param int val          the relative value to resize in pixel
     */
    sizeCtl: function(editor,val){
        var $textarea = jQuery(editor);
        var height = parseInt($textarea.css('height'));
        height += val;
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
        var $textarea = jQuery(editor);
        var wrap = textarea.attr('wrap');
        if(wrap && wrap.toLowerCase() == 'off'){
            dw_editor.setWrap(textarea[0], 'soft');
        }else{
            dw_editor.setWrap(textarea[0], 'off');
        }
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
     * Currently handles space, backspce and enter presses
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     * @fixme handle tabs
     * @param event e - the key press event object
     */
    keyHandler: function(e){
        if(e.keyCode != 13 &&
           e.keyCode != 8  &&
           e.keyCode != 32) return;
        var field     = e.target;
        var selection = getSelection(field);
        if(selection.getLength()) return; //there was text selected, keep standard behavior
        var search    = "\n"+field.value.substr(0,selection.start);
        var linestart = Math.max(search.lastIndexOf("\n"),
                                 search.lastIndexOf("\r")); //IE workaround
        search = search.substr(linestart);

        if(e.keyCode == 13){ // Enter
            // keep current indention for lists and code
            var match = search.match(/(\n  +([\*-] ?)?)/);
            if(match){
                var scroll = field.scrollHeight;
                var match2 = search.match(/^\n  +[\*-]\s*$/);
                // Cancel list if the last item is empty (i. e. two times enter)
                if (match2 && field.value.substr(selection.start).match(/^($|\r?\n)/)) {
                    field.value = field.value.substr(0, linestart) + "\n" +
                                  field.value.substr(selection.start);
                    selection.start = linestart + 1;
                    selection.end = linestart + 1;
                    setSelection(selection);
                } else {
                    insertAtCarret(field.id,match[1]);
                }
                field.scrollTop += (field.scrollHeight - scroll);
                e.preventDefault(); // prevent enter key
                return false;
            }
        }else if(e.keyCode == 8){ // Backspace
            // unindent lists
            var match = search.match(/(\n  +)([*-] ?)$/);
            if(match){
                var spaces = match[1].length-1;

                if(spaces > 3){ // unindent one level
                    field.value = field.value.substr(0,linestart)+
                                  field.value.substr(linestart+2);
                    selection.start = selection.start - 2;
                    selection.end   = selection.start;
                }else{ // delete list point
                    field.value = field.value.substr(0,linestart)+
                                  field.value.substr(selection.start);
                    selection.start = linestart;
                    selection.end   = linestart;
                }
                setSelection(selection);
                e.preventDefault(); // prevent backspace
                return false;
            }
        }else if(e.keyCode == 32){ // Space
            // intend list item
            var match = search.match(/(\n  +)([*-] )$/);
            if(match){
                field.value = field.value.substr(0,linestart)+'  '+
                              field.value.substr(linestart);
                selection.start = selection.start + 2;
                selection.end   = selection.start;
                setSelection(selection);
                e.preventDefault(); // prevent space
                return false;
            }
        }
    }


};

jQuery(dw_editor.init);
