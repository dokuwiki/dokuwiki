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
        var editor = jQuery('#wiki__text');
        if(!editor.length) return;

        dw_editor.initSizeCtl('#size__ctl',editor);
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
        var ctl      = jQuery(ctlarea);
        var textarea = jQuery(editor);
        if(!ctl.length || !textarea.length) return;

        var hgt = DokuCookie.getValue('sizeCtl');
        if(hgt){
            textarea.css('height', hgt);
        }else{
            textarea.css('height', '300px');
        }

        var wrp = DokuCookie.getValue('wrapCtl');
        if(wrp){
            dw_editor.setWrap(textarea[0], wrp);
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
        ctl.append(l);
        ctl.append(s);
        ctl.append(w);
    },

    /**
     * This sets the vertical size of the editbox and adjusts the cookie
     *
     * @param selector editor  the textarea to control
     * @param int val          the relative value to resize in pixel
     */
    sizeCtl: function(editor,val){
        var textarea = jQuery(editor);
        var height = parseInt(textarea.css('height'));
        height += val;
        textarea.css('height', height+'px');
        DokuCookie.setValue('sizeCtl',textarea.css('height'));
    },

    /**
     * Toggle the wrapping mode of the editor textarea and adjusts the
     * cookie
     *
     * @param selector editor  the textarea to control
     */
    toggleWrap: function(editor){
        var textarea = jQuery(editor);
        var wrap = textarea.attr('wrap');
        if(wrap && wrap.toLowerCase() == 'off'){
            dw_editor.setWrap(textarea[0], 'soft');
        }else{
            dw_editor.setWrap(textarea[0], 'off');
        }
        DokuCookie.setValue('wrapCtl',textarea.attr('wrap'));
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
    }


};

jQuery(dw_editor.init);
