/**
 * The Link Wizard
 *
 * @author Andreas Gohr <gohr@cosmocode.de>
 */
var linkwiz = {
    wiz:    null,
    entry:  null,
    result: null,
    timer:  null,
    sack:   null,
    textArea: null,
    selected: -1,
    selection: null,

    /**
     * Initialize the linkwizard by creating the needed HTML
     * and attaching the eventhandlers
     */
    init: function(textArea){
        // prepare AJAX object
        linkwiz.sack = new sack(DOKU_BASE + 'lib/exe/ajax.php');
        linkwiz.sack.AjaxFailedAlert = '';
        linkwiz.sack.encodeURIString = false;

        // create HTML Structure
        linkwiz.wiz = document.createElement('div');
        linkwiz.wiz.id = 'link__wiz';
        linkwiz.wiz.className     = 'picker';
        linkwiz.wiz.style.top  = (findPosY(textArea)+20)+'px';
        linkwiz.wiz.style.left = (findPosX(textArea)+80)+'px';
        linkwiz.wiz.style.marginLeft = '-10000px';
        linkwiz.wiz.style.marginTop  = '-10000px';
        linkwiz.wiz.style.position = 'absolute';

        linkwiz.wiz.innerHTML =
             '<div id="link__wiz_header">'+
             '<img src="'+DOKU_BASE+'lib/images/close.png" width="16" height="16" align="right" alt="" id="link__wiz_close" />'+
             LANG['linkwiz']+'</div>'+
             '<div>'+LANG['linkto']+' <input type="text" class="edit" id="link__wiz_entry" autocomplete="off" /></div>'+
             '<div id="link__wiz_result"></div>';
        $('dw__editform').parentNode.appendChild(linkwiz.wiz);
        linkwiz.textArea = textArea;
        linkwiz.result = $('link__wiz_result');
        linkwiz.entry = $('link__wiz_entry');

        // attach event handlers
        var obj;
        obj = $('link__wiz_close');
        obj.onclick = linkwiz.hide;

        linkwiz.sack.elementObj = linkwiz.result;
        addEvent(linkwiz.entry,'keyup',linkwiz.onEntry);
        addEvent(linkwiz.result,'click',linkwiz.onResultClick);
        drag.attach(linkwiz.wiz,$('link__wiz_header'));
    },

    /**
     * handle all keyup events in the entry field
     */
    onEntry: function(e){
        if(e.keyCode == 37 || e.keyCode == 39){ //left/right
            return true; //ignore
        }
        if(e.keyCode == 27){
            linkwiz.hide();
            e.preventDefault();
            e.stopPropagation();
            return false;
        }
        if(e.keyCode == 38){ //Up
            linkwiz.select(linkwiz.selected -1);
            e.preventDefault();
            e.stopPropagation();
            return false;
        }
        if(e.keyCode == 40){ //Down
            linkwiz.select(linkwiz.selected +1);
            e.preventDefault();
            e.stopPropagation();
            return false;
        }
        if(e.keyCode == 13){ //Enter
            if(linkwiz.selected > -1){
                var obj = linkwiz.getResult(linkwiz.selected);
                if(obj){
                    var a = obj.getElementsByTagName('A')[0];
                    linkwiz.resultClick(a);
                }
            }else if(linkwiz.entry.value){
                linkwiz.insertLink(linkwiz.entry.value);
            }

            e.preventDefault();
            e.stopPropagation();
            return false;
        }
        linkwiz.autocomplete();
    },

    /**
     * Get one of the result by index
     *
     * @param int result div to return
     * @returns DOMObject or null
     */
    getResult: function(num){
        var obj;
        var childs = linkwiz.result.getElementsByTagName('DIV');
        obj = childs[num];
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
            linkwiz.deselect();
            return;
        }

        var obj = linkwiz.getResult(num);
        if(obj){
            linkwiz.deselect();
            obj.className += ' selected';

            // make sure the item is viewable in the scroll view
            // FIXME check IE compatibility
            if(obj.offsetTop > linkwiz.result.scrollTop + linkwiz.result.clientHeight){
                linkwiz.result.scrollTop += obj.clientHeight;
            }else if(obj.offsetTop - linkwiz.result.clientHeight < linkwiz.result.scrollTop){ // this works but isn't quite right, fixes welcome
                linkwiz.result.scrollTop -= obj.clientHeight;
            }
            // now recheck - if still not in view, the user used the mouse to scroll
            if( (obj.offsetTop > linkwiz.result.scrollTop + linkwiz.result.clientHeight) ||
                (obj.offsetTop < linkwiz.result.scrollTop) ){
                obj.scrollIntoView();
            }

            linkwiz.selected = num;
        }
    },

    /**
     * deselect a result if any is selected
     */
    deselect: function(){
        if(linkwiz.selected > -1){
            var obj = linkwiz.getResult(linkwiz.selected);
            if(obj){
                obj.className = obj.className.replace(/ ?selected/,'');
            }
        }
        linkwiz.selected = -1;
    },

    /**
     * Handle clicks in the result set an dispatch them to
     * resultClick()
     */
    onResultClick: function(e){
        if(e.target.tagName != 'A') return;
        e.stopPropagation();
        e.preventDefault();
        linkwiz.resultClick(e.target);
        return false;
    },

    /**
     * Handles the "click" on a given result anchor
     */
    resultClick: function(a){
        var id = a.title;
        if(id == '' || id.substr(id.length-1) == ':'){
            linkwiz.entry.value = id;
            linkwiz.autocomplete_exec();
        }else{
            linkwiz.entry.value = id;
            if(a.nextSibling && a.nextSibling.tagName == 'SPAN'){
                linkwiz.insertLink(a.nextSibling.innerHTML);
            }else{
                linkwiz.insertLink('');
            }
        }
    },

    /**
     * Insert the id currently in the entry box to the textarea,
     * replacing the current selection or at the curso postion.
     * When no selection is available the given title will be used
     * as link title instead
     */
    insertLink: function(title){
        if(!linkwiz.entry.value) return;

        var sel = getSelection(linkwiz.textArea);
        if(sel.start == 0 && sel.end == 0) sel = linkwiz.selection;

        var stxt = sel.getText();

        // don't include trailing space in selection
        if(stxt.charAt(stxt.length - 1) == ' '){
            sel.end--;
            stxt = sel.getText();
        }

        if(!stxt && !DOKU_UHC) stxt=title;

        // prepend colon inside namespaces for non namespace pages
        if(linkwiz.textArea.form['id'].value.indexOf(':') != -1 &&
           linkwiz.entry.value.indexOf(':') == -1){
            linkwiz.entry.value = ':'+linkwiz.entry.value;
        }

        var link = '[['+linkwiz.entry.value+'|';
        if(stxt) link += stxt;
        link += ']]';

        var so = linkwiz.entry.value.length+3;
        var eo = 2;

        pasteText(sel,link,{startofs: so, endofs: eo});
        linkwiz.hide();
        // reset the entry to the parent namespace and remove : at the beginning
        linkwiz.entry.value = linkwiz.entry.value.replace(/(^:)?[^:]*$/, '');
    },

    /**
     * Start the page/namespace lookup timer
     *
     * Calls autocomplete_exec when the timer runs out
     */
    autocomplete: function(){
        if(linkwiz.timer !== null){
            window.clearTimeout(linkwiz.timer);
            linkwiz.timer = null;
        }

        linkwiz.timer = window.setTimeout(linkwiz.autocomplete_exec,350);
    },

    /**
     * Executes the AJAX call for the page/namespace lookup
     */
    autocomplete_exec: function(){
        linkwiz.deselect();
        linkwiz.result.innerHTML = '<img src="'+DOKU_BASE+'lib/images/throbber.gif" alt="" width="16" height="16" />';
        linkwiz.sack.runAJAX('call=linkwiz&q='+encodeURI(linkwiz.entry.value));
    },

    /**
     * Clears the result area
     */
    clear: function(){
        linkwiz.result.innerHTML = 'Search for a matching page name above, or browse through the pages on the right';
        linkwiz.entry.value = '';
    },

    /**
     * Show the linkwizard
     */
    show: function(){
        linkwiz.selection  = getSelection(linkwiz.textArea);
        linkwiz.wiz.style.marginLeft = '0px';
        linkwiz.wiz.style.marginTop = '0px';
        linkwiz.entry.focus();
        linkwiz.autocomplete();
    },

    /**
     * Hide the link wizard
     */
    hide: function(){
        linkwiz.wiz.style.marginLeft = '-10000px';
        linkwiz.wiz.style.marginTop  = '-10000px';
        linkwiz.textArea.focus();
    },

    /**
     * Toggle the link wizard
     */
    toggle: function(){
        if(linkwiz.wiz.style.marginLeft == '-10000px'){
            linkwiz.show();
        }else{
            linkwiz.hide();
        }
    }
};

