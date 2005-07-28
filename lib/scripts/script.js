/**
 * Some of these scripts were taken from wikipedia.org and were modified for DokuWiki
 */

/**
 * Some browser detection
 */
var clientPC  = navigator.userAgent.toLowerCase(); // Get client info
var is_gecko  = ((clientPC.indexOf('gecko')!=-1) && (clientPC.indexOf('spoofer')==-1)
                && (clientPC.indexOf('khtml') == -1) && (clientPC.indexOf('netscape/7.0')==-1));
var is_safari = ((clientPC.indexOf('AppleWebKit')!=-1) && (clientPC.indexOf('spoofer')==-1));
var is_khtml  = (navigator.vendor == 'KDE' || ( document.childNodes && !document.all && !navigator.taintEnabled ));
if (clientPC.indexOf('opera')!=-1) {
    var is_opera = true;
    var is_opera_preseven = (window.opera && !document.childNodes);
    var is_opera_seven = (window.opera && document.childNodes);
}


/**
 * This function escapes some special chars
 */
function escapeQuotes(text) {
  var re=new RegExp("'","g");
  text=text.replace(re,"\\'");
  re=new RegExp('"',"g");
  text=text.replace(re,'&quot;');
  re=new RegExp("\\n","g");
  text=text.replace(re,"\\n");
  return text;
}

/**
 * Prints a animated gif to show the search is performed
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function showLoadBar(){
  if(document.getElementById){
    document.write('<img src="'+DOKU_BASE+'lib/images/loading.gif" '+
                   'width="150" height="12" id="loading" />');
  }
}

/**
 * Disables the animated gif to show the search is done
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function hideLoadBar(){
  if(document.getElementById){
    document.getElementById('loading').style.display="none";
  }
}

/**
 * Checks if a summary was entered - if not the style is changed
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function summaryCheck(){
  if(document.getElementById){
    var sum = document.getElementById('summary');
    if(sum.value == ''){
      sum.className='missing';
    }else{
      sum.className='edit';
    }
  }
}

/**
 * This function generates the actual toolbar buttons with localized text
 * we use it to avoid creating the toolbar where javascript is not enabled
 */
function formatButton(imageFile, speedTip, tagOpen, tagClose, sampleText, accessKey) {
  speedTip=escapeQuotes(speedTip);
  tagOpen=escapeQuotes(tagOpen);
  tagClose=escapeQuotes(tagClose);
  sampleText=escapeQuotes(sampleText);

  document.write("<a ");
  if(accessKey){
    document.write("accesskey=\""+accessKey+"\" ");
    speedTip = speedTip+' [ALT+'+accessKey.toUpperCase()+']';
  }
  document.write("href=\"javascript:insertTags");
  document.write("('"+tagOpen+"','"+tagClose+"','"+sampleText+"');\">");

  document.write("<img width=\"24\" height=\"24\" src=\""+
                DOKU_BASE+'lib/images/toolbar/'+imageFile+"\" border=\"0\" alt=\""+
                speedTip+"\" title=\""+speedTip+"\">");
  document.write("</a>");
  return;
}

/**
 * This function generates the actual toolbar buttons with localized text
 * we use it to avoid creating the toolbar where javascript is not enabled
 */
function insertButton(imageFile, speedTip, value, accessKey) {
  speedTip=escapeQuotes(speedTip);
  value=escapeQuotes(value);

  document.write("<a ");
  if(accessKey){
    document.write("accesskey=\""+accessKey+"\" ");
    speedTip = speedTip+' [ALT+'+accessKey.toUpperCase()+']';
  }
  document.write("href=\"javascript:insertAtCarret");
  document.write("(document.editform.wikitext,'"+value+"');\">");

  document.write("<img width=\"24\" height=\"24\" src=\""+
                DOKU_BASE+'lib/images/toolbar/'+imageFile+"\" border=\"0\" alt=\""+
                speedTip+"\" title=\""+speedTip+"\">");
  document.write("</a>");
  return;
}

/**
 * This adds a button for the MediaSelection Popup
 */
function mediaButton(imageFile, speedTip, accessKey, namespace) {
  speedTip=escapeQuotes(speedTip);
  document.write("<a ");
  if(accessKey){
    document.write("accesskey=\""+accessKey+"\" ");
  }
  document.write("href=\"javascript:void(window.open('"+DOKU_BASE+"lib/exe/media.php?ns="+
                 namespace+"','mediaselect','width=600,height=320,left=70,top=50,scrollbars=yes,resizable=yes'));\">");
  document.write("<img width=\"24\" height=\"24\" src=\""+
                 DOKU_BASE+'lib/images/toolbar/'+imageFile+"\" border=\"0\" alt=\""+
                 speedTip+"\" title=\""+speedTip+"\">");
  document.write("</a>");
  return;
}

/**
 * apply tagOpen/tagClose to selection in textarea, use sampleText instead
 * of selection if there is none copied and adapted from phpBB
 *
 * @author phpBB development team
 * @author MediaWiki development team
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Jim Raynor <jim_raynor@web.de>
 */
function insertTags(tagOpen, tagClose, sampleText) {
  var txtarea = document.editform.wikitext;
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
    text = theSelection;
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
    var replaced = false;
    var startPos = txtarea.selectionStart;
    var endPos   = txtarea.selectionEnd;
    if(endPos - startPos) replaced = true;
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
    var text;
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
  if (txtarea.createTextRange) txtarea.caretPos = document.selection.createRange().duplicate();
}


/*
 * Insert the selected filename and close the window
 *
 * @see http://www.alexking.org/index.php?content=software/javascript/content.php
 */
function mediaSelect(file){
  insertAtCarret(opener.document.editform.wikitext,'{{'+file+'}}');
  window.close(); 
}

/*
 * Insert the given value at the current cursor position
 *
 * @see http://www.alexking.org/index.php?content=software/javascript/content.php
 */
function insertAtCarret(field,value){
  //IE support
  if (document.selection) {
    field.focus();
    if(opener == null){
      sel = document.selection.createRange();
    }else{
      sel = opener.document.selection.createRange();
    }
    sel.text = value;
  //MOZILLA/NETSCAPE support
  }else if (field.selectionStart || field.selectionStart == '0') {
    var startPos  = field.selectionStart;
    var endPos    = field.selectionEnd;
    var scrollTop = field.scrollTop;
    field.value = field.value.substring(0, startPos)
                  + value
                  + field.value.substring(endPos, field.value.length);

    field.focus();
    var cPos=startPos+(value.length);
    field.selectionStart=cPos;
    field.selectionEnd=cPos;
    field.scrollTop=scrollTop;
  } else {
    field.value += "\n"+value;
  }
  // reposition cursor if possible
  if (field.createTextRange) field.caretPos = document.selection.createRange().duplicate();
}

/**
 * For the upload Dialog. Prefills the wikiname.
 */
function suggestWikiname(){
  var file = document.upload.upload.value;

  file = file.substr(file.lastIndexOf('/')+1);
  file = file.substr(file.lastIndexOf('\\')+1);

  document.upload.id.value = file;
}

/**
 * This prints the switch to toggle the Table of Contents
 */
function showTocToggle(showtxt,hidetxt) {
  if(document.getElementById) {
		show = '<img src="'+DOKU_BASE+'lib/images/arrow_down.gif" alt="'+showtxt+'">';
		hide = '<img src="'+DOKU_BASE+'lib/images/arrow_up.gif" alt="'+hidetxt+'">';

    document.writeln('<div class=\'toctoggle\'><a href="javascript:toggleToc()" class="toc">' +
    '<span id="showlink" style="display:none;">' + show + '</span>' +
    '<span id="hidelink">' + hide + '</span>'
    + '</a></div>');
  }
}

/**
 * This toggles the visibility of the Table of Contents
 */
function toggleToc() {
  var toc = document.getElementById('tocinside');
  var showlink=document.getElementById('showlink');
  var hidelink=document.getElementById('hidelink');
  if(toc.style.display == 'none') {
    toc.style.display = tocWas;
    hidelink.style.display='';
    showlink.style.display='none';
  } else {
    tocWas = toc.style.display;
    toc.style.display = 'none';
    hidelink.style.display='none';
    showlink.style.display='';

  }
}

/**
 * Sizecontrol inspired by TikiWiki. This displays the buttons.
 */
function showSizeCtl(){
  if(document.getElementById) {
    var textarea = document.getElementById('wikitext');
    var hgt = getCookie('DokuWikisizeCtl');
    if(hgt == null){
      textarea.style.height = '300px';
    }else{
      textarea.style.height = hgt;
    }
    document.writeln('<a href="javascript:sizeCtl(100)"><img src="'+DOKU_BASE+'lib/images/larger.gif" width="20" height="20" border="0"></a>');
    document.writeln('<a href="javascript:sizeCtl(-100)"><img src="'+DOKU_BASE+'lib/images/smaller.gif" width="20" height="20" border="0"></a>');
  }
}

/**
 * This sets the vertical size of the editbox
 */
function sizeCtl(val){
  var textarea = document.getElementById('wikitext');
  var height = parseInt(textarea.style.height.substr(0,textarea.style.height.length-2));
  height += val;
  textarea.style.height = height+'px';
  
  var now = new Date();
  fixDate(now);
  now.setTime(now.getTime() + 365 * 24 * 60 * 60 * 1000); //expire in a year
  setCookie('DokuWikisizeCtl',textarea.style.height,now);
}


/**
 * global var used for not saved yet warning
 */
var textChanged = false;

function svchk(){
  if(textChanged){
    return confirm(notSavedYet);
  }else{
    return true;
  }
}

/**
 * global variable for the locktimer
 */
var locktimerID;

/**
 * This starts a timer to remind the user of an expiring lock
 * Accepts the delay in seconds and a text to display.
 */
function init_locktimer(delay,txt){
  txt = escapeQuotes(txt);
  locktimerID = self.setTimeout("locktimer('"+txt+"')", delay*1000);
}

/**
 * This stops the timer and displays a message about the expiring lock
 */
function locktimer(txt){
  clearTimeout(locktimerID);
  alert(txt);
}

/*
 * This sets a cookie by JavaScript
 *
 * @see http://www.webreference.com/js/column8/functions.html
 */
function setCookie(name, value, expires, path, domain, secure) {
  var curCookie = name + "=" + escape(value) +
      ((expires) ? "; expires=" + expires.toGMTString() : "") +
      ((path) ? "; path=" + path : "") +
      ((domain) ? "; domain=" + domain : "") +
      ((secure) ? "; secure" : "");
  document.cookie = curCookie;
}

/*
 * This reads a cookie by JavaScript
 *
 * @see http://www.webreference.com/js/column8/functions.html
 */
function getCookie(name) {
  var dc = document.cookie;
  var prefix = name + "=";
  var begin = dc.indexOf("; " + prefix);
  if (begin == -1) {
    begin = dc.indexOf(prefix);
    if (begin != 0) return null;
  } else
    begin += 2;
  var end = document.cookie.indexOf(";", begin);
  if (end == -1)
    end = dc.length;
  return unescape(dc.substring(begin + prefix.length, end));
}

/*
 * This is needed for the cookie functions
 *
 * @see http://www.webreference.com/js/column8/functions.html
 */
function fixDate(date) {
  var base = new Date(0);
  var skew = base.getTime();
  if (skew > 0)
    date.setTime(date.getTime() - skew);
}

/*
 * This enables/disables checkboxes for acl-administration
 *
 * @author Frank Schubert <frank@schokilade.de>
 */
function checkAclLevel(){
  if(document.getElementById) {
    var scope = document.getElementById('acl_scope').value;

    //check for namespace
    if( (scope.indexOf(":*") > 0) || (scope == "*") ){
      document.getElementsByName('acl_checkbox[4]')[0].disabled=false;
      document.getElementsByName('acl_checkbox[8]')[0].disabled=false;
    }else{
      document.getElementsByName('acl_checkbox[4]')[0].checked=false;
      document.getElementsByName('acl_checkbox[8]')[0].checked=false;

      document.getElementsByName('acl_checkbox[4]')[0].disabled=true;
      document.getElementsByName('acl_checkbox[8]')[0].disabled=true;
    }
  }
}

/* insitu footnote addition
 * provide a wrapper for domTT javascript library
 * this function is placed in the onmouseover event of footnote references in the main page
 * 
 * @author Chris Smith <chris [at] jalakai [dot] co [dot] uk>
 */
var currentFootnote = 0;
function fnt(id, e, evt) {

    if (currentFootnote && id != currentFootnote) {
        domTT_close(document.getElementById('insitu-fn'+currentFootnote));
    }
    
    // does the footnote tooltip already exist?
    var fnt = document.getElementById('insitu-fn'+id);
    if (!fnt) {
        // if not create it...
    
        // locate the footnote anchor element
        var a = document.getElementById( "fn"+id );    
        if (!a) return;
        
        // anchor parent is the footnote container, get its innerHTML
        var footnote = new String (a.parentNode.innerHTML);
        
        // strip the leading footnote anchors and their comma separators
        footnote = footnote.replace(/<a\s.*?href=\".*\#fnt\d+\".*?<\/a>/gi, '');
        footnote = footnote.replace(/^\s+(,\s+)+/,'');
        
        // prefix ids on any elements with "insitu-" to ensure they remain unique
        footnote = footnote.replace(/\bid=\"(.*?)\"/gi,'id="insitu-$1');
   	} else {
        var footnote = new String(fnt.innerHTML);
    }
    
    // activate the tooltip
    domTT_activate(e, evt, 'content', footnote, 'type', 'velcro', 'id', 'insitu-fn'+id, 'styleClass', 'insitu-footnote', 'maxWidth', document.body.offsetWidth*0.4);
    currentFootnote = id;    
}
