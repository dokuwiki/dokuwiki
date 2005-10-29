/**
 * Some of these scripts were taken from wikipedia.org and were modified for DokuWiki
 */

/**
 * Some browser detection
 */
var clientPC  = navigator.userAgent.toLowerCase(); // Get client info
var is_gecko  = ((clientPC.indexOf('gecko')!=-1) && (clientPC.indexOf('spoofer')==-1) &&
                (clientPC.indexOf('khtml') == -1) && (clientPC.indexOf('netscape/7.0')==-1));
var is_safari = ((clientPC.indexOf('AppleWebKit')!=-1) && (clientPC.indexOf('spoofer')==-1));
var is_khtml  = (navigator.vendor == 'KDE' || ( document.childNodes && !document.all && !navigator.taintEnabled ));
if (clientPC.indexOf('opera')!=-1) {
    var is_opera = true;
    var is_opera_preseven = (window.opera && !document.childNodes);
    var is_opera_seven = (window.opera && document.childNodes);
}

/**
 * Get the X offset of the top left corner of the given object
 *
 * @link http://www.quirksmode.org/index.html?/js/findpos.html
 */
function findPosX(object){
  var curleft = 0;
  var obj;
  if(typeof(object) == 'object'){
    obj = object;
  }else{
    obj = document.getElementById(object);
  }
  if (obj.offsetParent){
    while (obj.offsetParent){
      curleft += obj.offsetLeft;
      obj = obj.offsetParent;
    }
  }
  else if (obj.x){
    curleft += obj.x;
  }
  return curleft;
} //end findPosX function

/**
 * Get the Y offset of the top left corner of the given object
 *
 * @link http://www.quirksmode.org/index.html?/js/findpos.html
 */
function findPosY(object){
  var curtop = 0;
  var obj;
  if(typeof(object) == 'object'){
    obj = object;
  }else{
    obj = document.getElementById(object);
  }
  if (obj.offsetParent){
    while (obj.offsetParent){
      curtop += obj.offsetTop;
      obj = obj.offsetParent;
    }
  }
  else if (obj.y){
    curtop += obj.y;
  }
  return curtop;
} //end findPosY function

/**
 * Escape special chars in JavaScript
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function jsEscape(text){
    var re=new RegExp("\\\\","g");
    text=text.replace(re,"\\\\");
    re=new RegExp("'","g");
    text=text.replace(re,"\\'");
    re=new RegExp('"',"g");
    text=text.replace(re,'&quot;');
    re=new RegExp("\\\\\\\\n","g");
    text=text.replace(re,"\\n");
    return text;
}

/**
 * This function escapes some special chars
 * @deprecated by above function
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

/*
 * Insert the selected filename and close the window
 *
 * @see http://www.alexking.org/index.php?content=software/javascript/content.php
 */
function mediaSelect(file){
	opener.insertAtCarret('wikitext','{{'+file+'}}');
  window.close(); 
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
    '<span id="hidelink">' + hide + '</span>' +
    '</a></div>');
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
    if (begin !== 0){ return null; }
  } else {
    begin += 2;
  }
  var end = document.cookie.indexOf(";", begin);
  if (end == -1){
    end = dc.length;
  }
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
  if (skew > 0){
    date.setTime(date.getTime() - skew);
  }
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
    var fnote = document.getElementById('insitu-fn'+id);
    var footnote;
    if (!fnote) {
        // if not create it...
    
        // locate the footnote anchor element
        var a = document.getElementById( "fn"+id );    
        if (!a){ return; }
        
        // anchor parent is the footnote container, get its innerHTML
        footnote = new String (a.parentNode.innerHTML);
        
        // strip the leading footnote anchors and their comma separators
        footnote = footnote.replace(/<a\s.*?href=\".*\#fnt\d+\".*?<\/a>/gi, '');
        footnote = footnote.replace(/^\s+(,\s+)+/,'');
        
        // prefix ids on any elements with "insitu-" to ensure they remain unique
        footnote = footnote.replace(/\bid=\"(.*?)\"/gi,'id="insitu-$1');
   	} else {
        footnote = new String(fnt.innerHTML);
    }
    
    // activate the tooltip
    domTT_activate(e, evt, 'content', footnote, 'type', 'velcro', 'id', 'insitu-fn'+id, 'styleClass', 'insitu-footnote JSpopup', 'maxWidth', document.body.offsetWidth*0.4);
    currentFootnote = id;    
}


/**
 * Add the edit window size controls
 */
function initSizeCtl(ctlid,edid){
		if(!document.getElementById){ return; }

    var ctl      = document.getElementById(ctlid);
    var textarea = document.getElementById(edid);

    var hgt = getCookie('DokuWikisizeCtl');
    if(hgt === null || hgt === ''){
      textarea.style.height = '300px';
    }else{
      textarea.style.height = hgt;
    }

    var l = document.createElement('img');
    var s = document.createElement('img');
    l.src = DOKU_BASE+'lib/images/larger.gif';
    s.src = DOKU_BASE+'lib/images/smaller.gif';
		addEvent(l,'click',function(){sizeCtl(edid,100);});
		addEvent(s,'click',function(){sizeCtl(edid,-100);});
    ctl.appendChild(l);
    ctl.appendChild(s);
}

/**
 * This sets the vertical size of the editbox
 */
function sizeCtl(edid,val){
  var textarea = document.getElementById(edid);
  var height = parseInt(textarea.style.height.substr(0,textarea.style.height.length-2));
  height += val;
  textarea.style.height = height+'px';

  var now = new Date();
  fixDate(now);
  now.setTime(now.getTime() + 365 * 24 * 60 * 60 * 1000); //expire in a year
  setCookie('DokuWikisizeCtl',textarea.style.height,now);
}

/**
 * Handler to close all open Popups
 */
function closePopups(){
  if(!document.getElementById){ return; }

  var divs = document.getElementsByTagName('div');
  for(var i=0; i < divs.length; i++){
    if(divs[i].className.indexOf('JSpopup') != -1){
			divs[i].style.display = 'none';
    }
  }
}
