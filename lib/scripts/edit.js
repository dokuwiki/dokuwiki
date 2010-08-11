/**
 * Functions for text editing (toolbar stuff)
 *
 * @todo most of the stuff in here should be revamped and then moved to toolbar.js
 * @author Andreas Gohr <andi@splitbrain.org>
 */

/**
 * Creates a toolbar button through the DOM
 *
 * Style the buttons through the toolbutton class
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function createToolButton(icon,label,key,id,classname){
    var btn = document.createElement('button');
    var ico = document.createElement('img');

    // preapare the basic button stuff
    btn.className = 'toolbutton';
    if(classname){
        btn.className += ' '+classname;
    }
    btn.title = label;
    if(key){
        btn.title += ' ['+key.toUpperCase()+']';
        btn.accessKey = key;
    }

    // set IDs if given
    if(id){
        btn.id = id;
        ico.id = id+'_ico';
    }

    // create the icon and add it to the button
    if(icon.substr(0,1) == '/'){
        ico.src = icon;
    }else{
        ico.src = DOKU_BASE+'lib/images/toolbar/'+icon;
    }
    btn.appendChild(ico);

    return btn;
}

/**
 * Creates a picker window for inserting text
 *
 * The given list can be an associative array with text,icon pairs
 * or a simple list of text. Style the picker window through the picker
 * class or the picker buttons with the pickerbutton class. Picker
 * windows are appended to the body and created invisible.
 *
 * @param  string id    the ID to assign to the picker
 * @param  array  props the properties for the picker
 * @param  string edid  the ID of the textarea
 * @rteurn DOMobject    the created picker
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function createPicker(id,props,edid){
    var icobase = props['icobase'];
    var list    = props['list'];

    // create the wrapping div
    var picker            = document.createElement('div');
    picker.className      = 'picker';
    if(props['class']){
        picker.className += ' '+props['class'];
    }
    picker.id               = id;
    picker.style.position   = 'absolute';
    picker.style.marginLeft = '-10000px'; // no display:none, to keep access keys working
    picker.style.marginTop  = '-10000px';

    for(var key in list){
        if (!list.hasOwnProperty(key)) continue;

        if(isNaN(key)){
            // associative array -> treat as image/value pairs
            var btn = document.createElement('button');
            btn.className = 'pickerbutton';
            var ico = document.createElement('img');
            if(list[key].substr(0,1) == '/'){
                ico.src = list[key];
            }else{
                ico.src = DOKU_BASE+'lib/images/'+icobase+'/'+list[key];
            }
            btn.title     = key;
            btn.appendChild(ico);
            addEvent(btn,'click',bind(pickerInsert,key,edid));
            picker.appendChild(btn);
        }else if(isString(list[key])){
            // a list of text -> treat as text picker
            var btn = document.createElement('button');
            btn.className = 'pickerbutton';
            var txt = document.createTextNode(list[key]);
            btn.title     = list[key];
            btn.appendChild(txt);
            addEvent(btn,'click',bind(pickerInsert,list[key],edid));
            picker.appendChild(btn);
        }else{
            // a list of lists -> treat it as subtoolbar
            initToolbar(picker,edid,list);
            break; // all buttons handled already
        }

    }
    var body = document.getElementsByTagName('body')[0];
    body.appendChild(picker);
    return picker;
}

/**
 * Called by picker buttons to insert Text and close the picker again
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function pickerInsert(text,edid){
    insertAtCarret(edid,text);
    pickerClose();
}

/**
 * Add button action for signature button
 *
 * @param  DOMElement btn   Button element to add the action to
 * @param  array      props Associative array of button properties
 * @param  string     edid  ID of the editor textarea
 * @return boolean    If button should be appended
 * @author Gabriel Birke <birke@d-scribe.de>
 */
function addBtnActionSignature(btn, props, edid) {
    if(typeof(SIG) != 'undefined' && SIG != ''){
        addEvent(btn,'click',bind(insertAtCarret,edid,SIG));
        return true;
    }
    return false;
}

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
 */
function keyHandler(e){
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

//FIXME consolidate somewhere else
addInitEvent(function(){
    var field = $('wiki__text');
    if(!field) return;
    // in Firefox, keypress doesn't send the correct keycodes,
    // in Opera, the default of keydown can't be prevented
    if (is_opera) {
        addEvent(field,'keypress',keyHandler);
    } else {
        addEvent(field,'keydown',keyHandler);
    }
});

/**
 * Determine the current section level while editing
 *
 * @author Andreas Gohr <gohr@cosmocode.de>
 */
function currentHeadlineLevel(textboxId){
    var field     = $(textboxId);
    var selection = getSelection(field);
    var search    = "\n"+field.value.substr(0,selection.start);
    var lasthl    = search.lastIndexOf("\n==");
    if(lasthl == -1 && field.form.prefix){
        // we need to look in prefix context
        search = field.form.prefix.value;
        lasthl    = search.lastIndexOf("\n==");
    }
    search    = search.substr(lasthl+1,6);

    if(search == '======') return 1;
    if(search.substr(0,5) == '=====') return 2;
    if(search.substr(0,4) == '====') return 3;
    if(search.substr(0,3) == '===') return 4;
    if(search.substr(0,2) == '==') return 5;

    return 0;
}


/**
 * global var used for not saved yet warning
 */
var textChanged = false;

/**
 * Delete the draft before leaving the page
 */
function deleteDraft() {
    if (is_opera) return;

    // remove a possibly saved draft using ajax
    var dwform = $('dw__editform');
    if(dwform){
        var params = 'call=draftdel';
        params += '&id='+encodeURIComponent(dwform.elements.id.value);

        var sackobj = new sack(DOKU_BASE + 'lib/exe/ajax.php');
        // this needs to be synchronous and GET to not be aborted upon page unload
        sackobj.asynchronous = false;
        sackobj.method = 'GET';
        sackobj.AjaxFailedAlert = '';
        sackobj.encodeURIString = false;
        sackobj.runAJAX(params);
    }
}

/**
 * Activate "not saved" dialog, add draft deletion to page unload,
 * add handlers to monitor changes
 *
 * Sets focus to the editbox as well
 */
addInitEvent(function (){
    var editform = $('dw__editform');
    if (!editform) return;

    var edit_text   = $('wiki__text');
    if(edit_text) {
        if(edit_text.readOnly) return;

        // set focus
        edit_text.focus();
    }

    var checkfunc = function(){
        textChanged = true; //global var
        summaryCheck();
    };
    addEvent(editform, 'change', checkfunc);
    addEvent(editform, 'keydown', checkfunc);

    window.onbeforeunload = function(){
        if(textChanged) {
            return LANG.notsavedyet;
        }
    };
    window.onunload = deleteDraft;

    // reset change memory var on submit
    addEvent($('edbtn__save'), 'click', function(){ textChanged = false; });
    addEvent($('edbtn__preview'), 'click', function(){ textChanged = false; });

    var summary = $('edit__summary');
    addEvent(summary, 'change', summaryCheck);
    addEvent(summary, 'keyup', summaryCheck);
    if (textChanged) summaryCheck();
});

/**
 * Checks if a summary was entered - if not the style is changed
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function summaryCheck(){
    var sum = document.getElementById('edit__summary');
    if(sum.value === ''){
        sum.className='missing';
    }else{
        sum.className='edit';
    }
}


/**
 * Class managing the timer to display a warning on a expiring lock
 */
function locktimer_class(){
        this.sack     = null;
        this.timeout  = 0;
        this.timerID  = null;
        this.lasttime = null;
        this.msg      = '';
        this.pageid   = '';
};
var locktimer = new locktimer_class();
    locktimer.init = function(timeout,msg,draft){
        // init values
        locktimer.timeout  = timeout*1000;
        locktimer.msg      = msg;
        locktimer.draft    = draft;
        locktimer.lasttime = new Date();

        if(!$('dw__editform')) return;
        locktimer.pageid = $('dw__editform').elements.id.value;
        if(!locktimer.pageid) return;

        // init ajax component
        locktimer.sack = new sack(DOKU_BASE + 'lib/exe/ajax.php');
        locktimer.sack.AjaxFailedAlert = '';
        locktimer.sack.encodeURIString = false;
        locktimer.sack.onCompletion = locktimer.refreshed;

        // register refresh event
        addEvent($('dw__editform'),'keypress',function(){locktimer.refresh();});
        // start timer
        locktimer.reset();
    };

    /**
     * (Re)start the warning timer
     */
    locktimer.reset = function(){
        locktimer.clear();
        locktimer.timerID = window.setTimeout("locktimer.warning()", locktimer.timeout);
    };

    /**
     * Display the warning about the expiring lock
     */
    locktimer.warning = function(){
        locktimer.clear();
        alert(locktimer.msg);
    };

    /**
     * Remove the current warning timer
     */
    locktimer.clear = function(){
        if(locktimer.timerID !== null){
            window.clearTimeout(locktimer.timerID);
            locktimer.timerID = null;
        }
    };

    /**
     * Refresh the lock via AJAX
     *
     * Called on keypresses in the edit area
     */
    locktimer.refresh = function(){
        var now = new Date();
        // refresh every minute only
        if(now.getTime() - locktimer.lasttime.getTime() > 30*1000){ //FIXME decide on time
            var params = 'call=lock&id='+encodeURIComponent(locktimer.pageid);
            var dwform = $('dw__editform');
            if(locktimer.draft && dwform.elements.wikitext){
                params += '&prefix='+encodeURIComponent(dwform.elements.prefix.value);
                params += '&wikitext='+encodeURIComponent(dwform.elements.wikitext.value);
                params += '&suffix='+encodeURIComponent(dwform.elements.suffix.value);
                if(dwform.elements.date){
                    params += '&date='+encodeURIComponent(dwform.elements.date.value);
                }
            }
            locktimer.sack.runAJAX(params);
            locktimer.lasttime = now;
        }
    };


    /**
     * Callback. Resets the warning timer
     */
    locktimer.refreshed = function(){
        var data  = this.response;
        var error = data.charAt(0);
            data  = data.substring(1);

        $('draft__status').innerHTML=data;
        if(error != '1') return; // locking failed
        locktimer.reset();
    };
// end of locktimer class functions

