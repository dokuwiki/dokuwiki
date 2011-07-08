// if jQuery was loaded, let's make it noConflict here.
if ('function' === typeof jQuery && 'function' === typeof jQuery.noConflict) {
    jQuery.noConflict();
}

/**
 * Mark a JavaScript function as deprecated
 *
 * This will print a warning to the JavaScript console (if available) in
 * Firebug and Chrome and a stack trace (if available) to easily locate the
 * problematic function call.
 *
 * @param msg optional message to print
 */
function DEPRECATED(msg){
    if(!window.console) return;
    if(!msg) msg = '';

    var func;
    if(arguments.callee) func = arguments.callee.caller.name;
    if(func) func = ' '+func+'()';
    var line = 'DEPRECATED function call'+func+'. '+msg;

    if(console.warn){
        console.warn(line);
    }else{
        console.log(line);
    }

    if(console.trace) console.trace();
}

/**
 * Construct a wrapper function for deprecated function names
 *
 * This function returns a wrapper function which just calls DEPRECATED
 * and the new function.
 *
 * @param func    The new function
 * @param context Optional; The context (`this`) of the call
 */
function DEPRECATED_WRAP(func, context) {
    return function () {
        DEPRECATED();
        return func.apply(context || this, arguments);
    }
}

/**
 * Some of these scripts were taken from wikipedia.org and were modified for DokuWiki
 */

/**
 * Some browser detection
 */
var clientPC  = navigator.userAgent.toLowerCase(); // Get client info
var is_macos  = navigator.appVersion.indexOf('Mac') != -1;
var is_gecko  = ((clientPC.indexOf('gecko')!=-1) && (clientPC.indexOf('spoofer')==-1) &&
                (clientPC.indexOf('khtml') == -1) && (clientPC.indexOf('netscape/7.0')==-1));
var is_safari = ((clientPC.indexOf('applewebkit')!=-1) && (clientPC.indexOf('spoofer')==-1));
var is_khtml  = (navigator.vendor == 'KDE' || ( document.childNodes && !document.all && !navigator.taintEnabled ));
if (clientPC.indexOf('opera')!=-1) {
    var is_opera = true;
    var is_opera_preseven = (window.opera && !document.childNodes);
    var is_opera_seven = (window.opera && document.childNodes);
}

/**
 * Handy shortcut to document.getElementById
 *
 * This function was taken from the prototype library
 *
 * @link http://prototype.conio.net/
 */
function $() {
  DEPRECATED('Please use the JQuery() function instead.');

  var elements = new Array();

  for (var i = 0; i < arguments.length; i++) {
    var element = arguments[i];
    if (typeof element == 'string')
      element = document.getElementById(element);

    if (arguments.length == 1)
      return element;

    elements.push(element);
  }

  return elements;
}

/**
 * Simple function to check if a global var is defined
 *
 * @author Kae Verens
 * @link http://verens.com/archives/2005/07/25/isset-for-javascript/#comment-2835
 */
function isset(varname){
  return(typeof(window[varname])!='undefined');
}

/**
 * Get the computed style of a node.
 *
 * @link https://acidmartin.wordpress.com/2008/08/26/style-get-any-css-property-value-of-an-object/
 * @link http://svn.dojotoolkit.org/src/dojo/trunk/_base/html.js
 */
function gcs(node){
    if(node.currentStyle){
        return node.currentStyle;
    }else{
        return node.ownerDocument.defaultView.getComputedStyle(node, null);
    }
}

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
 * Adds a node as the first childenode to the given parent
 *
 * @see appendChild()
 */
function prependChild(parent,element) {
    if(!parent.firstChild){
        parent.appendChild(element);
    }else{
        parent.insertBefore(element,parent.firstChild);
    }
}

/**
 * Prints a animated gif to show the search is performed
 *
 * Because we need to modify the DOM here before the document is loaded
 * and parsed completely we have to rely on document.write()
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function showLoadBar(){

  document.write('<img src="'+DOKU_BASE+'lib/images/loading.gif" '+
                 'width="150" height="12" alt="..." />');

  /* this does not work reliable in IE
  obj = $(id);

  if(obj){
    obj.innerHTML = '<img src="'+DOKU_BASE+'lib/images/loading.gif" '+
                    'width="150" height="12" alt="..." />';
    obj.style.display="block";
  }
  */
}

/**
 * Disables the animated gif to show the search is done
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function hideLoadBar(id){
  obj = $(id);
  if(obj) obj.style.display="none";
}

/**
 * Adds the toggle switch to the TOC
 */
function addTocToggle() {
    if(!document.getElementById) return;
    var header = $('toc__header');
    if(!header) return;
    var toc = $('toc__inside');

    var obj          = document.createElement('span');
    obj.id           = 'toc__toggle';
    obj.style.cursor = 'pointer';
    if (toc && toc.style.display == 'none') {
        obj.innerHTML    = '<span>+</span>';
        obj.className    = 'toc_open';
    } else {
        obj.innerHTML    = '<span>&minus;</span>';
        obj.className    = 'toc_close';
    }

    prependChild(header,obj);
    obj.parentNode.onclick = toggleToc;
    obj.parentNode.style.cursor = 'pointer';
}

/**
 * This toggles the visibility of the Table of Contents
 */
function toggleToc() {
  var toc = $('toc__inside');
  var obj = $('toc__toggle');
  if(toc.style.display == 'none') {
    toc.style.display   = '';
    obj.innerHTML       = '<span>&minus;</span>';
    obj.className       = 'toc_close';
  } else {
    toc.style.display   = 'none';
    obj.innerHTML       = '<span>+</span>';
    obj.className       = 'toc_open';
  }
}

/**
 * Create JavaScript mouseover popup
 */
function insitu_popup(target, popup_id) {

    // get or create the popup div
    var fndiv = $(popup_id);
    if(!fndiv){
        fndiv = document.createElement('div');
        fndiv.id        = popup_id;
        fndiv.className = 'insitu-footnote JSpopup dokuwiki';

        // autoclose on mouseout - ignoring bubbled up events
        addEvent(fndiv,'mouseout',function(e){
            var p = e.relatedTarget || e.toElement;
            while (p && p !== this) {
                p = p.parentNode;
            }
            if (p === this) {
                return;
            }
            // okay, hide it
            this.style.display='none';
        });
        getElementsByClass('dokuwiki', document.body, 'div')[0].appendChild(fndiv);
    }

    var non_static_parent = fndiv.parentNode;
    while (non_static_parent != document && gcs(non_static_parent)['position'] == 'static') {
        non_static_parent = non_static_parent.parentNode;
    }

    var fixed_target_parent = target;
    while (fixed_target_parent != document && gcs(fixed_target_parent)['position'] != 'fixed') {
        fixed_target_parent = fixed_target_parent.parentNode;
    }

    // position the div and make it visible
    if (fixed_target_parent != document) {
        // the target has position fixed, that means the footnote needs to be fixed, too
        fndiv.style.position = 'fixed';
    } else {
        fndiv.style.position = 'absolute';
    }

    if (fixed_target_parent != document || non_static_parent == document) {
        fndiv.style.left = findPosX(target)+'px';
        fndiv.style.top  = (findPosY(target)+target.offsetHeight * 1.5) + 'px';
    } else {
        fndiv.style.left = (findPosX(target) - findPosX(non_static_parent)) +'px';
        fndiv.style.top  = (findPosY(target)+target.offsetHeight * 1.5 - findPosY(non_static_parent)) + 'px';
    }

    fndiv.style.display = '';
    return fndiv;
}

/**
 * Display an insitu footnote popup
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Chris Smith <chris@jalakai.co.uk>
 */
function footnote(e){
    var fndiv = insitu_popup(e.target, 'insitu__fn');

    // locate the footnote anchor element
    var a = $("fn__" + e.target.id.substr(5));
    if (!a){ return; }

    // anchor parent is the footnote container, get its innerHTML
    var content = new String (a.parentNode.parentNode.innerHTML);

    // strip the leading content anchors and their comma separators
    content = content.replace(/<sup>.*<\/sup>/gi, '');
    content = content.replace(/^\s+(,\s+)+/,'');

    // prefix ids on any elements with "insitu__" to ensure they remain unique
    content = content.replace(/\bid=(['"])([^"']+)\1/gi,'id="insitu__$2');

    // now put the content into the wrapper
    fndiv.innerHTML = content;
}

/**
 * Add the event handlers to footnotes
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
addInitEvent(function(){
    var elems = getElementsByClass('fn_top',null,'a');
    for(var i=0; i<elems.length; i++){
        addEvent(elems[i],'mouseover',function(e){footnote(e);});
    }
});

/**
 * Add the edit window size controls
 */
function initSizeCtl(ctlid,edid){
    if(!document.getElementById){ return; }

    var ctl      = $(ctlid);
    var textarea = $(edid);
    if(!ctl || !textarea) return;

    var hgt = DokuCookie.getValue('sizeCtl');
    if(hgt){
      textarea.style.height = hgt;
    }else{
      textarea.style.height = '300px';
    }

    var wrp = DokuCookie.getValue('wrapCtl');
    if(wrp){
      setWrap(textarea, wrp);
    } // else use default value

    var l = document.createElement('img');
    var s = document.createElement('img');
    var w = document.createElement('img');
    l.src = DOKU_BASE+'lib/images/larger.gif';
    s.src = DOKU_BASE+'lib/images/smaller.gif';
    w.src = DOKU_BASE+'lib/images/wrap.gif';
    addEvent(l,'click',function(){sizeCtl(edid,100);});
    addEvent(s,'click',function(){sizeCtl(edid,-100);});
    addEvent(w,'click',function(){toggleWrap(edid);});
    ctl.appendChild(l);
    ctl.appendChild(s);
    ctl.appendChild(w);
}

/**
 * This sets the vertical size of the editbox
 */
function sizeCtl(edid,val){
  var textarea = $(edid);
  var height = parseInt(textarea.style.height.substr(0,textarea.style.height.length-2));
  height += val;
  textarea.style.height = height+'px';

  DokuCookie.setValue('sizeCtl',textarea.style.height);
}

/**
 * Toggle the wrapping mode of a textarea
 */
function toggleWrap(edid){
    var textarea = $(edid);
    var wrap = textarea.getAttribute('wrap');
    if(wrap && wrap.toLowerCase() == 'off'){
        setWrap(textarea, 'soft');
    }else{
        setWrap(textarea, 'off');
    }

    DokuCookie.setValue('wrapCtl',textarea.getAttribute('wrap'));
}

/**
 * Set the wrapping mode of a textarea
 *
 * @author Fluffy Convict <fluffyconvict@hotmail.com>
 * @author <shutdown@flashmail.com>
 * @link   http://news.hping.org/comp.lang.javascript.archive/12265.html
 * @link   https://bugzilla.mozilla.org/show_bug.cgi?id=41464
 */
function setWrap(textarea, wrapAttrValue){
    textarea.setAttribute('wrap', wrapAttrValue);

    // Fix display for mozilla
    var parNod = textarea.parentNode;
    var nxtSib = textarea.nextSibling;
    parNod.removeChild(textarea);
    parNod.insertBefore(textarea, nxtSib);
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

/**
 * disable multiple revisions checkboxes if two are checked
 *
 * @author Anika Henke <anika@selfthinker.org>
 */
addInitEvent(function(){
    var revForm = $('page__revisions');
    if (!revForm) return;
    var elems = revForm.elements;
    var countTicks = 0;
    for (var i=0; i<elems.length; i++) {
        var input1 = elems[i];
        if (input1.type=='checkbox') {
            addEvent(input1,'click',function(e){
                if (this.checked) countTicks++;
                else countTicks--;
                for (var j=0; j<elems.length; j++) {
                    var input2 = elems[j];
                    if (countTicks >= 2) input2.disabled = (input2.type=='checkbox' && !input2.checked);
                    else input2.disabled = (input2.type!='checkbox');
                }
            });
            input1.checked = false; // chrome reselects on back button which messes up the logic
        } else if(input1.type=='submit'){
            input1.disabled = true;
        }
    }
});


/**
 * Highlight the section when hovering over the appropriate section edit button
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
addInitEvent(function(){
    var btns = getElementsByClass('btn_secedit',document,'form');
    for(var i=0; i<btns.length; i++){
        addEvent(btns[i],'mouseover',function(e){
            var tgt = this.parentNode;
            var nr = tgt.className.match(/(\s+|^)editbutton_(\d+)(\s+|$)/)[2];
            do {
                tgt = tgt.previousSibling;
            } while (tgt !== null && typeof tgt.tagName === 'undefined');
            if (tgt === null) return;
            while(typeof tgt.className === 'undefined' ||
                  tgt.className.match('(\\s+|^)sectionedit' + nr + '(\\s+|$)') === null) {
                if (typeof tgt.className !== 'undefined') {
                    tgt.className += ' section_highlight';
                }
                tgt = (tgt.previousSibling !== null) ? tgt.previousSibling : tgt.parentNode;
            }
            if (typeof tgt.className !== 'undefined') tgt.className += ' section_highlight';
        });

        addEvent(btns[i],'mouseout',function(e){
            var secs = getElementsByClass('section_highlight');
            for(var j=0; j<secs.length; j++){
                secs[j].className = secs[j].className.replace(/section_highlight/g,'');
            }
        });
    }
});

