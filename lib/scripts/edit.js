/**
 * Functions for text editing (toolbar stuff)
 *
 * @todo I'm no JS guru please help if you know how to improve
 * @author Andreas Gohr <andi@splitbrain.org>
 */

/**
 * Creates a toolbar button through the DOM
 *
 * Style the buttons through the toolbutton class
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function createToolButton(icon,label,key,id){
    var btn = document.createElement('button');
    var ico = document.createElement('img');

    // preapare the basic button stuff
    btn.className = 'toolbutton';
    btn.title = label;
    if(key){
        btn.title += ' [ALT+'+key.toUpperCase()+']';
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
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function createPicker(id,list,icobase,edid){
    var cnt = list.length;

    var picker = document.createElement('div');
    picker.className = 'picker';
    picker.id = id;
    picker.style.position = 'absolute';
    picker.style.display  = 'none';

    for(var key in list){
        if (!list.hasOwnProperty(key)) continue;
        var btn = document.createElement('button');

        btn.className = 'pickerbutton';

        // associative array?
        if(isNaN(key)){
            var ico = document.createElement('img');
            if(list[key].substr(0,1) == '/'){
                ico.src = list[key];
            }else{
                ico.src = DOKU_BASE+'lib/images/'+icobase+'/'+list[key];
            }
            btn.title     = key;
            btn.appendChild(ico);
            eval("btn.onclick = function(){pickerInsert('"+id+"','"+
                                  jsEscape(key)+"','"+
                                  jsEscape(edid)+"');return false;}");
        }else{
            var txt = document.createTextNode(list[key]);
            btn.title     = list[key];
            btn.appendChild(txt);
            eval("btn.onclick = function(){pickerInsert('"+id+"','"+
                                  jsEscape(list[key])+"','"+
                                  jsEscape(edid)+"');return false;}");
        }

        picker.appendChild(btn);
    }
    var body = document.getElementsByTagName('body')[0];
    body.appendChild(picker);
}

/**
 * Called by picker buttons to insert Text and close the picker again
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function pickerInsert(pickerid,text,edid){
    // insert
    insertAtCarret(edid,text);
    // close picker
    pobj = document.getElementById(pickerid);
    pobj.style.display = 'none';
}

/**
 * Show a previosly created picker window
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function showPicker(pickerid,btn){
    var picker = document.getElementById(pickerid);
    var x = findPosX(btn);
    var y = findPosY(btn);
    if(picker.style.display == 'none'){
        picker.style.display = 'block';
        picker.style.left = (x+3)+'px';
        picker.style.top = (y+btn.offsetHeight+3)+'px';
    }else{
        picker.style.display = 'none';
    }
}

/**
 * Create a toolbar
 *
 * @param  string tbid ID of the element where to insert the toolbar
 * @param  string edid ID of the editor textarea
 * @param  array  tb   Associative array defining the buttons
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function initToolbar(tbid,edid,tb){
    var toolbar = $(tbid);
    if(!toolbar) return;

    //empty the toolbar area:
    toolbar.innerHTML='';

    var cnt = tb.length;
    for(var i=0; i<cnt; i++){
        // create new button
        var btn = createToolButton(tb[i]['icon'],
                               tb[i]['title'],
                               tb[i]['key']);

        var actionFunc = 'addBtnAction'+tb[i]['type'].charAt(0).toUpperCase()+tb[i]['type'].substring(1);
        var exists = eval("typeof("+actionFunc+") == 'function'");
        if(exists)
        {
            if(eval(actionFunc+"(btn, tb[i], edid, i)"))
                toolbar.appendChild(btn);
        }
    } // end for
}

/**
 * Add button action for format buttons
 *
 * @param  DOMElement btn   Button element to add the action to
 * @param  array      props Associative array of button properties
 * @param  string     edid  ID of the editor textarea
 * @return boolean    If button should be appended
 * @author Gabriel Birke <birke@d-scribe.de>
 */
function addBtnActionFormat(btn, props, edid)
{
    var sample = props['title'];
    if(props['sample']){ sample = props['sample']; }
    eval("btn.onclick = function(){insertTags('"+
        jsEscape(edid)+"','"+
        jsEscape(props['open'])+"','"+
        jsEscape(props['close'])+"','"+
        jsEscape(sample)+
    "');return false;}");

    return true;
}

/**
 * Add button action for insert buttons
 *
 * @param  DOMElement btn   Button element to add the action to
 * @param  array      props Associative array of button properties
 * @param  string     edid  ID of the editor textarea
 * @return boolean    If button should be appended
 * @author Gabriel Birke <birke@d-scribe.de>
 */
function addBtnActionInsert(btn, props, edid)
{
    eval("btn.onclick = function(){insertAtCarret('"+
        jsEscape(edid)+"','"+
        jsEscape(props['insert'])+
    "');return false;}");
    return true;
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
function addBtnActionSignature(btn, props, edid)
{
    if(typeof(SIG) != 'undefined' && SIG != ''){
        eval("btn.onclick = function(){insertAtCarret('"+
            jsEscape(edid)+"','"+
            jsEscape(SIG)+
        "');return false;}");
        return true;
    }
    return false;
}

/**
 * Add button action for picker buttons and create picker element
 *
 * @param  DOMElement btn   Button element to add the action to
 * @param  array      props Associative array of button properties
 * @param  string     edid  ID of the editor textarea
 * @param  int        id    Unique number of the picker
 * @return boolean    If button should be appended
 * @author Gabriel Birke <birke@d-scribe.de>
 */
function addBtnActionPicker(btn, props, edid, id)
{
    createPicker('picker'+id,
         props['list'],
         props['icobase'],
         edid);
    eval("btn.onclick = function(){showPicker('picker"+id+
                                    "',this);return false;}");
    return true;
}

/**
 * Add button action for the mediapopup button
 *
 * @param  DOMElement btn   Button element to add the action to
 * @param  array      props Associative array of button properties
 * @return boolean    If button should be appended
 * @author Gabriel Birke <birke@d-scribe.de>
 */
function addBtnActionMediapopup(btn, props)
{
    eval("btn.onclick = function(){window.open('"+
        jsEscape(props['url']+encodeURIComponent(NS))+"','"+
        jsEscape(props['name'])+"','"+
        jsEscape(props['options'])+
    "');return false;}");
    return true;
}

/**
 * Format selection
 *
 * Apply tagOpen/tagClose to selection in textarea, use sampleText instead
 * of selection if there is none. Copied and adapted from phpBB
 *
 * @author phpBB development team
 * @author MediaWiki development team
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Jim Raynor <jim_raynor@web.de>
 */
function insertTags(edid,tagOpen, tagClose, sampleText) {
  var txtarea = document.getElementById(edid);
  // IE
  if(document.selection  && !is_gecko) {
    var theSelection = document.selection.createRange().text;
    var replaced = true;
    if(!theSelection){
      replaced = false;
      theSelection=sampleText;
    }
    txtarea.focus();

    // This has change
    var text = theSelection;
    if(theSelection.charAt(theSelection.length - 1) == " "){// exclude ending space char, if any
      theSelection = theSelection.substring(0, theSelection.length - 1);
      r = document.selection.createRange();
      r.text = tagOpen + theSelection + tagClose + " ";
    } else {
      r = document.selection.createRange();
      r.text = tagOpen + theSelection + tagClose;
    }
    if(!replaced){
      r.moveStart('character',-text.length-tagClose.length);
      r.moveEnd('character',-tagClose.length);
    }
    r.select();
  // Mozilla
  } else if(txtarea.selectionStart || txtarea.selectionStart == '0') {
    replaced = false;
    var startPos = txtarea.selectionStart;
    var endPos   = txtarea.selectionEnd;
    if(endPos - startPos){ replaced = true; }
    var scrollTop=txtarea.scrollTop;
    var myText = (txtarea.value).substring(startPos, endPos);
    if(!myText) { myText=sampleText;}
    if(myText.charAt(myText.length - 1) == " "){ // exclude ending space char, if any
      subst = tagOpen + myText.substring(0, (myText.length - 1)) + tagClose + " ";
    } else {
      subst = tagOpen + myText + tagClose;
    }
    txtarea.value = txtarea.value.substring(0, startPos) + subst +
                    txtarea.value.substring(endPos, txtarea.value.length);
    txtarea.focus();

    //set new selection
    if(replaced){
      var cPos=startPos+(tagOpen.length+myText.length+tagClose.length);
      txtarea.selectionStart=cPos;
      txtarea.selectionEnd=cPos;
    }else{
      txtarea.selectionStart=startPos+tagOpen.length;
      txtarea.selectionEnd=startPos+tagOpen.length+myText.length;
    }
    txtarea.scrollTop=scrollTop;
  // All others
  } else {
    var copy_alertText=alertText;
    var re1=new RegExp("\\$1","g");
    var re2=new RegExp("\\$2","g");
    copy_alertText=copy_alertText.replace(re1,sampleText);
    copy_alertText=copy_alertText.replace(re2,tagOpen+sampleText+tagClose);

    if (sampleText) {
      text=prompt(copy_alertText);
    } else {
      text="";
    }
    if(!text) { text=sampleText;}
    text=tagOpen+text+tagClose;
    //append to the end
    txtarea.value += "\n"+text;

    // in Safari this causes scrolling
    if(!is_safari) {
      txtarea.focus();
    }

  }
  // reposition cursor if possible
  if (txtarea.createTextRange){
    txtarea.caretPos = document.selection.createRange().duplicate();
  }
}

/*
 * Insert the given value at the current cursor position
 *
 * @see http://www.alexking.org/index.php?content=software/javascript/content.php
 */
function insertAtCarret(edid,value){
  var field = document.getElementById(edid);

  //IE support
  if (document.selection) {
    field.focus();
    sel = document.selection.createRange();
    sel.text = value;

  //MOZILLA/NETSCAPE support
  }else if (field.selectionStart || field.selectionStart == '0') {
    var startPos  = field.selectionStart;
    var endPos    = field.selectionEnd;
    var scrollTop = field.scrollTop;
    field.value = field.value.substring(0, startPos) +
                  value +
                  field.value.substring(endPos, field.value.length);

    field.focus();
    var cPos=startPos+(value.length);
    field.selectionStart=cPos;
    field.selectionEnd=cPos;
    field.scrollTop=scrollTop;
  } else {
    field.value += "\n"+value;
  }
  // reposition cursor if possible
  if (field.createTextRange){
    field.caretPos = document.selection.createRange().duplicate();
  }
}


/**
 * global var used for not saved yet warning
 */
var textChanged = false;

/**
 * Check for changes before leaving the page
 */
function changeCheck(msg){
  if(textChanged){
    var ok = confirm(msg);
    if(ok){
        // remove a possibly saved draft using ajax
        var dwform = $('dw__editform');
        if(dwform){
            var params = 'call=draftdel';
            params += '&id='+encodeURIComponent(dwform.elements.id.value);

            var sackobj = new sack(DOKU_BASE + 'lib/exe/ajax.php');
            sackobj.AjaxFailedAlert = '';
            sackobj.encodeURIString = false;
            sackobj.runAJAX(params);
            // we send this request blind without waiting for
            // and handling the returned data
        }
    }
    return ok;
  }else{
    return true;
  }
}

/**
 * Add changeCheck to all Links and Forms (except those with a
 * JSnocheck class), add handlers to monitor changes
 *
 * Sets focus to the editbox as well
 */
function initChangeCheck(msg){
    if(!document.getElementById){ return false; }
    // add change check for links
    var links = document.getElementsByTagName('a');
    for(var i=0; i < links.length; i++){
        if(links[i].className.indexOf('JSnocheck') == -1){
            links[i].onclick = function(){
                                    var rc = changeCheck(msg);
                                    if(window.event) window.event.returnValue = rc;
                                    return rc;
                               };
            links[i].onkeypress = function(){
                                    var rc = changeCheck(msg);
                                    if(window.event) window.event.returnValue = rc;
                                    return rc;
                               };
        }
    }
    // add change check for forms
    var forms = document.forms;
    for(i=0; i < forms.length; i++){
        if(forms[i].className.indexOf('JSnocheck') == -1){
            forms[i].onsubmit = function(){
                                    var rc = changeCheck(msg);
                                    if(window.event) window.event.returnValue = rc;
                                    return rc;
                               };
        }
    }

    // reset change memory var on submit
    var btn_save        = document.getElementById('edbtn__save');
    btn_save.onclick    = function(){ textChanged = false; };
    btn_save.onkeypress = function(){ textChanged = false; };
    var btn_prev        = document.getElementById('edbtn__preview');
    btn_prev.onclick    = function(){ textChanged = false; };
    btn_prev.onkeypress = function(){ textChanged = false; };

    // add change memory setter
    var edit_text   = document.getElementById('wiki__text');
    edit_text.onchange = function(){
        textChanged = true; //global var
        summaryCheck();
    };
    edit_text.onkeyup  = summaryCheck;
    var summary = document.getElementById('edit__summary');
    addEvent(summary, 'change', summaryCheck);
    addEvent(summary, 'keyup', summaryCheck);
    if (textChanged) summaryCheck();

    // set focus
    edit_text.focus();
}

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
        addEvent($('dw__editform').elements.wikitext,'keyup',function(){locktimer.refresh();});

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
            if(locktimer.draft){
                var dwform = $('dw__editform');
                params += '&prefix='+encodeURIComponent(dwform.elements.prefix.value);
                params += '&wikitext='+encodeURIComponent(dwform.elements.wikitext.value);
                params += '&suffix='+encodeURIComponent(dwform.elements.suffix.value);
                params += '&date='+encodeURIComponent(dwform.elements.date.value);
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

