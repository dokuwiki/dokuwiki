/*jslint sloppy: true, indent: 4, white: true, browser: true, eqeq: true */
/*global jQuery, DOKU_BASE, LANG, DOKU_UHC, getSelection, pasteText */


/**
 * The Link Wizard
 *
 * @author Andreas Gohr <gohr@cosmocode.de>
 * @author Pierre Spring <pierre.spring@caillou.ch>
 */
var dw_linkwiz = {
    $wiz: null,
    entry: null,
    result: null,
    timer: null,
    textArea: null,
    selected: null,
    selection: null,

    /**
     * Initialize the dw_linkwizard by creating the needed HTML
     * and attaching the eventhandlers
     */
    init: function($editor){
        // position relative to the text area
        var pos = $editor.position();
        pos.left += 20;
        pos.right += 80;

        // create HTML Structure
        dw_linkwiz.$wiz = jQuery(document.createElement('div'))
               .attr('id','link__wiz')
               .css({
                    'position':    'absolute',
                    'top':         pos.left+'px',
                    'left':        pos.top+'px',
                    'margin-left': '-10000px',
                    'margin-top':  '-10000px'
                   })
               .html(
                    '<div id="link__wiz_header">'+
                    '<img src="'+DOKU_BASE+'lib/images/close.png" width="16" height="16" align="right" alt="" id="link__wiz_close" />'+
                    LANG.linkwiz+'</div>'+
                    '<div>'+LANG.linkto+' <input type="text" class="edit" id="link__wiz_entry" autocomplete="off" /></div>'+
                    '<div id="link__wiz_result"></div>'
                    )
               .addClass('picker');

        $editor[0].form.parentNode.appendChild(dw_linkwiz.$wiz[0]);
        dw_linkwiz.textArea = $editor[0];
        dw_linkwiz.result = jQuery('#link__wiz_result')[0];
        dw_linkwiz.entry = jQuery('#link__wiz_entry')[0];

        // attach event handlers
        jQuery('#link__wiz_close').click(dw_linkwiz.hide);
        jQuery(dw_linkwiz.entry).keyup(dw_linkwiz.onEntry);
        jQuery(dw_linkwiz.result).click(dw_linkwiz.onResultClick);

        dw_linkwiz.$wiz.draggable({handle: '#link__wiz_header'});
    },

    /**
     * handle all keyup events in the entry field
     */
    onEntry: function(e){
        if(e.keyCode == 37 || e.keyCode == 39){ //left/right
            return true; //ignore
        }
        if(e.keyCode == 27){
            dw_linkwiz.hide();
            e.preventDefault();
            e.stopPropagation();
            return false;
        }
        if(e.keyCode == 38){ //Up
            dw_linkwiz.select(dw_linkwiz.selected -1);
            e.preventDefault();
            e.stopPropagation();
            return false;
        }
        if(e.keyCode == 40){ //Down
            dw_linkwiz.select(dw_linkwiz.selected +1);
            e.preventDefault();
            e.stopPropagation();
            return false;
        }
        if(e.keyCode == 13){ //Enter
            if(dw_linkwiz.selected > -1){
                var obj = dw_linkwiz.getResult(dw_linkwiz.selected);
                if(obj){
                    var a = jQuery(obj).find('a')[0];
                    dw_linkwiz.resultClick(a);
                }
            }else if(dw_linkwiz.entry.value){
                dw_linkwiz.insertLink(dw_linkwiz.entry.value);
            }

            e.preventDefault();
            e.stopPropagation();
            return false;
        }
        dw_linkwiz.autocomplete();
    },

    /**
     * Get one of the results by index
     *
     * @param int result div to return
     * @returns DOMObject or null
     */
    getResult: function(num){
        var childs = jQuery(dw_linkwiz.result).find('div');
        var obj = childs[num];
        if(obj){
            return obj;
        }else{
            return null;
        }
    },

    /**
     * Select the given result
     */
    select: function(num){
        if(num < 0){
            dw_linkwiz.deselect();
            return;
        }

        var obj = dw_linkwiz.getResult(num);
        if(obj){
            dw_linkwiz.deselect();
            obj.className += ' selected';

            // make sure the item is viewable in the scroll view
            // FIXME check IE compatibility
            if(obj.offsetTop > dw_linkwiz.result.scrollTop + dw_linkwiz.result.clientHeight){
                dw_linkwiz.result.scrollTop += obj.clientHeight;
            }else if(obj.offsetTop - dw_linkwiz.result.clientHeight < dw_linkwiz.result.scrollTop){ // this works but isn't quite right, fixes welcome
                dw_linkwiz.result.scrollTop -= obj.clientHeight;
            }
            // now recheck - if still not in view, the user used the mouse to scroll
            if( (obj.offsetTop > dw_linkwiz.result.scrollTop + dw_linkwiz.result.clientHeight) ||
                (obj.offsetTop < dw_linkwiz.result.scrollTop) ){
                obj.scrollIntoView();
            }

            dw_linkwiz.selected = num;
        }
    },

    /**
     * deselect a result if any is selected
     */
    deselect: function(){
        if(dw_linkwiz.selected > -1){
            var obj = dw_linkwiz.getResult(dw_linkwiz.selected);
            if(obj){
                obj.className = obj.className.replace(/ ?selected/,'');
            }
        }
        dw_linkwiz.selected = -1;
    },

    /**
     * Handle clicks in the result set an dispatch them to
     * resultClick()
     */
    onResultClick: function(e){
        if(e.target.tagName != 'A') return;
        e.stopPropagation();
        e.preventDefault();
        dw_linkwiz.resultClick(e.target);
        return false;
    },

    /**
     * Handles the "click" on a given result anchor
     */
    resultClick: function(a){
        var id = a.title;
        if(id == '' || id.substr(id.length-1) == ':'){
            dw_linkwiz.entry.value = id;
            dw_linkwiz.autocomplete_exec();
        }else{
            dw_linkwiz.entry.value = id;
            if(a.nextSibling && a.nextSibling.tagName == 'SPAN'){
                dw_linkwiz.insertLink(a.nextSibling.innerHTML);
            }else{
                dw_linkwiz.insertLink('');
            }
        }
    },

    /**
     * Insert the id currently in the entry box to the textarea,
     * replacing the current selection or at the cursor position.
     * When no selection is available the given title will be used
     * as link title instead
     */
    insertLink: function(title){
        if(!dw_linkwiz.entry.value) return;

        var sel = getSelection(dw_linkwiz.textArea);
        if(sel.start == 0 && sel.end == 0) sel = dw_linkwiz.selection;

        var stxt = sel.getText();

        // don't include trailing space in selection
        if(stxt.charAt(stxt.length - 1) == ' '){
            sel.end--;
            stxt = sel.getText();
        }

        if(!stxt && !DOKU_UHC) stxt=title;

        // prepend colon inside namespaces for non namespace pages
        if(dw_linkwiz.textArea.form['id'].value.indexOf(':') != -1 &&
           dw_linkwiz.entry.value.indexOf(':') == -1){
            dw_linkwiz.entry.value = ':'+dw_linkwiz.entry.value;
        }

        var link = '[['+dw_linkwiz.entry.value+'|';
        if(stxt) link += stxt;
        link += ']]';

        var so = dw_linkwiz.entry.value.length+3;
        var eo = 2;

        pasteText(sel,link,{startofs: so, endofs: eo});
        dw_linkwiz.hide();
        // reset the entry to the parent namespace and remove : at the beginning
        dw_linkwiz.entry.value = dw_linkwiz.entry.value.replace(/(^:)?[^:]*$/, '');
    },

    /**
     * Start the page/namespace lookup timer
     *
     * Calls autocomplete_exec when the timer runs out
     */
    autocomplete: function(){
        if(dw_linkwiz.timer !== null){
            window.clearTimeout(dw_linkwiz.timer);
            dw_linkwiz.timer = null;
        }

        dw_linkwiz.timer = window.setTimeout(dw_linkwiz.autocomplete_exec,350);
    },

    /**
     * Executes the AJAX call for the page/namespace lookup
     */
    autocomplete_exec: function(){
        dw_linkwiz.deselect();
        dw_linkwiz.result.innerHTML = '<img src="'+DOKU_BASE+'lib/images/throbber.gif" alt="" width="16" height="16" />';

        // because we need to use POST, we
        // can not use the .load() function.
        jQuery.post(
            DOKU_BASE + 'lib/exe/ajax.php',
            {
                call: 'linkwiz',
                q: dw_linkwiz.entry.value
            },
            function (data) {
                dw_linkwiz.result.innerHTML = data;
            },
            'html'
        );
    },

    /**
     * Clears the result area
     * @fixme localize
     */
    clear: function(){
        dw_linkwiz.result.innerHTML = 'Search for a matching page name above, or browse through the pages on the right';
        dw_linkwiz.entry.value = '';
    },

    /**
     * Show the dw_linkwizard
     */
    show: function(){
        dw_linkwiz.selection  = getSelection(dw_linkwiz.textArea);
        dw_linkwiz.$wiz.css('marginLeft', '0');
        dw_linkwiz.$wiz.css('marginTop', '0');
        dw_linkwiz.entry.focus();
        dw_linkwiz.autocomplete();
    },

    /**
     * Hide the link wizard
     */
    hide: function(){
        dw_linkwiz.$wiz.css('marginLeft', '-10000px');
        dw_linkwiz.$wiz.css('marginTop', '-10000px');
        dw_linkwiz.textArea.focus();
    },

    /**
     * Toggle the link wizard
     */
    toggle: function(){
        if(dw_linkwiz.$wiz.css('marginLeft') == '-10000px'){
            dw_linkwiz.show();
        }else{
            dw_linkwiz.hide();
        }
    }

};
