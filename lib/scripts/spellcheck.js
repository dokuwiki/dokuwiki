/**
 * DokuWiki Spellcheck AJAX clientside script
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

/**
 * Licence info: This spellchecker is inspired by code by Garrison Locke available
 * at http://www.broken-notebook.com/spell_checker/index.php (licensed under the Terms
 * of an BSD license). The code in this file was nearly completly rewritten for DokuWiki
 * and is licensed under GPL version 2 (See COPYING for details).
 *
 * Original Copyright notice follows:
 *
 * Copyright (c) 2005, Garrison Locke
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 *   * Redistributions of source code must retain the above copyright notice,
 *     this list of conditions and the following disclaimer.
 *   * Redistributions in binary form must reproduce the above copyright notice,
 *     this list of conditions and the following disclaimer in the documentation
 *     and/or other materials provided with the distribution.
 *   * Neither the name of the http://www.broken-notebook.com nor the names of its
 *     contributors may be used to endorse or promote products derived from this
 *     software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
 * IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT,
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT
 * NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR
 * PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY,
 * WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY
 * OF SUCH DAMAGE.
 */

/*
 * Uses some general functions defined elsewhere. Here is a list:
 *
 * Defined in script.js:
 *
 *   findPosX()
 *   findPosY()
 *
 * Defined in events.js:
 *
 *   addEvent()
 *
 * Defined in edit.js:
 *
 *   createToolButton()
 */

/**
 * quotes single quotes
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function qquote(str){
  return str.split('\'').join('\\\'');
}

/**
 * AJAX Spellchecker Class
 *
 * Note to some function use a hardcoded instance named ajax_spell to make
 * references to object members. Used Object-IDs are hardcoded in the init()
 * method.
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Garrison Locke <http://www.broken-notebook.com>
 */
function ajax_spell_class(){
  this.inited = false;
  this.utf8ok = 1;
  this.handler = DOKU_BASE+'lib/exe/spellcheck.php';
  // to hold the page objects (initialized with init())
  this.textboxObj = null;
  this.showboxObj = null;
  this.suggestObj = null;
  this.editbarObj = null;
  this.buttonObj = null;
  this.imageObj  = null;

  // hold translations
  this.txtStart = 'Check Spelling';
  this.txtStop  = 'Resume Editing';
  this.txtRun   = 'Checking...';
  this.txtNoErr = 'No Mistakes';
  this.txtNoSug = 'No Suggestions';
  this.txtChange= 'Change';

  this.timer = null;

  /**
   * Initializes everything
   *
   * Call after the page was setup. Hardcoded element IDs here.
   *
   * @author Andreas Gohr <andi@splitbrain.org>
   */
  this.init = function(txtStart,txtStop,txtRun,txtNoErr,txtNoSug,txtChange){
     // don't run twice
    if (this.inited){ return; }
    this.inited = true;

    // check for AJAX availability
    var ajax = new sack(this.handler);
    if(ajax.failed){ return; }

    // get Elements
    this.textboxObj = document.getElementById('wiki__text');
    this.editbarObj = document.getElementById('wiki__editbar');
    this.showboxObj = document.getElementById('spell__result');
    this.suggestObj = document.getElementById('spell__suggest');


    // set wordwrap style with browser propritary attributes
    if(is_gecko){
      this.showboxObj.style.whiteSpace = '-moz-pre-wrap'; // Mozilla, since 1999
    }else if(is_opera_preseven){
      this.showboxObj.style.whiteSpace = '-pre-wrap';     // Opera 4-6
    }else if(is_opera_seven){
      this.showboxObj.style.whiteSpace = '-o-pre-wrap';   // Opera 7
    }else{
      this.showboxObj.style['word-wrap']   = 'break-word';    //Internet Explorer 5.5+
    }
    // Which browser supports this?
    // this.showboxObj.style.whiteSpace = 'pre-wrap';      // css-3


    // set Translation Strings
    this.txtStart = txtStart;
    this.txtStop  = txtStop;
    this.txtRun   = txtRun;
    this.txtNoErr = txtNoErr;
    this.txtNoSug = txtNoSug;
    this.txtChange= txtChange;

    // create ToolBar Button with ID and add it to the toolbar with null action
    var toolbarObj = document.getElementById('tool__bar');
    this.buttonObj = createToolButton('spellcheck.png',txtStart,'k','spell__check');
    this.buttonObj.onclick = function(){return false;};
    toolbarObj.appendChild(this.buttonObj);
    this.imageObj  = document.getElementById('spell__check_ico');

    // start UTF-8 compliance test - send an UTF-8 char and see what comes back
    ajax.AjaxFailedAlert = '';
    ajax.encodeURIString = false;
    ajax.onCompletion    = this.initReady;
    ajax.runAJAX('call=utf8test&data='+encodeURIComponent('ü'));

    // second part of initialisation is in initReady() function
  };

  /**
   * Eventhandler for click objects anywhere on the document
   *
   * Disables the suggestion box
   *
   * @author Andreas Gohr <andi@splitbrain.org>
   * @author Garrison Locke <http://www.broken-notebook.com>
   */
  this.docClick = function(e){
    // what was clicked?
    try{
      target = window.event.srcElement;
    }catch(ex){
      target = e.target;
    }

    if (target.id != ajax_spell.suggestObj.id){
      ajax_spell.suggestObj.style.display = "none";
    }
  };

  /**
   * Changes the Spellchecker link according to the given mode
   *
   * @author Andreas Gohr <andi@splitbrain.org>
   */
  this.setState = function(state){
    switch (state){
      case 'stop':
        ajax_spell.buttonObj.onclick   = function(){ ajax_spell.resume(); return false; };
        ajax_spell.buttonObj.title     = ajax_spell.txtStop;
        ajax_spell.buttonObj.accessKey = '';
        ajax_spell.imageObj.src = DOKU_BASE+'lib/images/toolbar/spellstop.png';
        break;
      case 'noerr':
        ajax_spell.buttonObj.onclick   = function(){ajax_spell.setState('start'); return false; };
        ajax_spell.buttonObj.title     = ajax_spell.txtNoErr;
        ajax_spell.buttonObj.accessKey = '';
        ajax_spell.imageObj.src = DOKU_BASE+'lib/images/toolbar/spellnoerr.png';
        break;
      case 'run':
        ajax_spell.buttonObj.onclick   = function(){return false;};
        ajax_spell.buttonObj.title     = ajax_spell.txtRun;
        ajax_spell.buttonObj.accessKey = '';
        ajax_spell.imageObj.src = DOKU_BASE+'lib/images/toolbar/spellwait.gif';
        break;
      default:
        ajax_spell.buttonObj.onclick   = function(){ ajax_spell.run(); return false; };
        ajax_spell.buttonObj.title     = ajax_spell.txtStart+' [ALT-K]';
        ajax_spell.buttonObj.accessKey = 'k';
        ajax_spell.imageObj.src = DOKU_BASE+'lib/images/toolbar/spellcheck.png';
        break;
    }
  };

  /**
   * Replaces a word identified by id with its correction given in word
   *
   * @author Garrison Locke <http://www.broken-notebook.com>
   */
  this.correct = function (id, word){
    var obj = document.getElementById('spell__error'+id);
    obj.innerHTML = decodeURIComponent(word);
    obj.style.color = "#005500";
    this.suggestObj.style.display = "none";
  };

  /**
   * Opens a prompt to let the user change the word her self
   *
   * @author Andreas Gohr <andi@splitbrain.org>
   */
  this.ask = function(id){
    var word = document.getElementById('spell__error'+id).innerHTML;
    word = prompt(this.txtChange,word);
    if(word){
      this.correct(id,encodeURIComponent(word));
    }
  };

  /**
   * Displays the suggestions for a misspelled word
   *
   * @author Andreas Gohr <andi@splitbrain.org>
   * @author Garrison Locke <http://www.broken-notebook.com>
   */
  this.suggest = function(){
    var args = this.suggest.arguments;
    if(!args[0]){ return; }
    var id   = args[0];

    // set position of the popup
    this.suggestObj.style.display = "none";
    var x = findPosX('spell__error'+id);
    var y = findPosY('spell__error'+id);

    // handle scrolling
    var scrollPos;
    if(is_opera){
      scrollPos = 0; //FIXME how to do this without browser sniffing?
    }else{
      scrollPos = this.showboxObj.scrollTop;
    }

    this.suggestObj.style.left = x+'px';
    this.suggestObj.style.top  = (y+16-scrollPos)+'px';

    // handle suggestions
    var text = '';
    if(args.length == 1){
      text += this.txtNoSug+'<br />';
    }else{
      for(var i=1; i<args.length; i++){
        text += '<a href="javascript:ajax_spell.correct('+id+',\''+
                qquote(args[i])+'\')">';
        text += args[i];
        text += '</a><br />';
      }
    }
    // add option for manual edit
    text += '<a href="javascript:ajax_spell.ask('+id+')">';
    text += '['+this.txtChange+']';
    text += '</a><br />';

    this.suggestObj.innerHTML = text;
    this.suggestObj.style.display = "block";
  };

  // --- Callbacks ---

  /**
   * Callback. Called after the object was initialized and UTF-8 tested
   * Inside the callback 'this' is the SACK object!!
   *
   * @author Andreas Gohr <andi@splitbrain.org>
   */
  this.initReady = function(){
    var data = this.response;

    //test for UTF-8 compliance (will fail for konqueror)
    if(data != 'ü'){
      ajax_spell.utf8ok = 0;
    }

    // register click event
    addEvent(document,'click',ajax_spell.docClick);

    // register focus event
    addEvent(ajax_spell.textboxObj,'focus',ajax_spell.setState);

    // get started
    ajax_spell.setState('start');
  };

  /**
   * Callback. Called after finishing spellcheck.
   * Inside the callback 'this' is the SACK object!!
   *
   * @author Andreas Gohr <andi@splitbrain.org>
   */
  this.start = function(){
    if(ajax_spell.timer !== null){
      window.clearTimeout(ajax_spell.timer);
      ajax_spell.timer = null;
    }else{
      // there is no timer set, we timed out already
      return;
    }

    var data  = this.response;
    var error = data.charAt(0);
        data  = data.substring(1);
    if(error == '1'){
      ajax_spell.setState('stop');

      // convert numeric entities back to UTF-8 if needed
      if(!ajax_spell.utf8ok){
        data = data.replace(/&#(\d+);/g,
                            function(whole,match1) {
                              return String.fromCharCode(+match1);
                            });
      }

      // replace textbox through div
      ajax_spell.showboxObj.innerHTML     = data;
      ajax_spell.showboxObj.style.width   = ajax_spell.textboxObj.style.width;
      ajax_spell.showboxObj.style.height  = ajax_spell.textboxObj.style.height;
      ajax_spell.textboxObj.style.display = 'none';
      ajax_spell.showboxObj.style.display = 'block';
    }else{
      if(error == '2'){
        alert(data);
      }
      ajax_spell.textboxObj.disabled = false;
      ajax_spell.editbarObj.style.visibility = 'visible';
      ajax_spell.setState('noerr');
    }
  };

  /**
   * Callback. Gets called by resume() - switches back to edit mode
   * Inside the callback 'this' is the SACK object!!
   *
   * @author Andreas Gohr <andi@splitbrain.org>
   */
  this.stop = function(){
    var data = this.response;

    // convert numeric entities back to UTF-8 if needed
    if(!ajax_spell.utf8ok){
      data = data.replace(/&#(\d+);/g,
                          function(whole,match1) {
                            return String.fromCharCode(+match1);
                          });
      // now remove &amp; protection
      data = data.replace(/&amp;/g,'&');
    }

    // replace div with textbox again
    ajax_spell.textboxObj.value         = data;
    ajax_spell.textboxObj.disabled      = false;
    ajax_spell.showboxObj.style.display = 'none';
    ajax_spell.textboxObj.style.display = 'block';
    ajax_spell.editbarObj.style.visibility = 'visible';
    ajax_spell.showboxObj.innerHTML     = '';
    ajax_spell.setState('start');
  };

  /**
   * Calback for the timeout handling
   *
   * Will be called when the aspell backend didn't return
   */
  this.timedOut = function(){
    if(ajax_spell.timer !== null){
      window.clearTimeout(ajax_spell.timer);
      ajax_spell.timer = null;

      ajax_spell.textboxObj.disabled      = false;
      ajax_spell.showboxObj.style.display = 'none';
      ajax_spell.textboxObj.style.display = 'block';
      ajax_spell.editbarObj.style.visibility = 'visible';
      ajax_spell.showboxObj.innerHTML     = '';
      ajax_spell.setState('start');

      window.alert('Error: The spell checker did not respond');
  }
  };

  // --- Callers ---

  /**
   * Starts the spellchecking by sending an AJAX request
   *
   * @author Andreas Gohr <andi@splitbrain.org>
   */
  this.run = function(){
    ajax_spell.setState('run');
    ajax_spell.textboxObj.disabled = true;
    ajax_spell.editbarObj.style.visibility = 'hidden';
    var ajax = new sack(ajax_spell.handler);
    ajax.AjaxFailedAlert = '';
    ajax.encodeURIString = false;
    ajax.onCompletion    = this.start;
    ajax.runAJAX('call=check&utf8='+ajax_spell.utf8ok+
                 '&data='+encodeURIComponent(ajax_spell.textboxObj.value));

    // abort after 13 seconds
    this.timer = window.setTimeout(ajax_spell.timedOut,13000);
  };

  /**
   * Rewrites the HTML back to text again using an AJAX request
   *
   * @author Andreas Gohr <andi@splitbrain.org>
   */
  this.resume = function(){
    ajax_spell.setState('run');
    var text = ajax_spell.showboxObj.innerHTML;
    if(text !== ''){
      var ajax = new sack(ajax_spell.handler);
      ajax.AjaxFailedAlert = '';
      ajax.encodeURIString = false;
      ajax.onCompletion    = ajax_spell.stop;
      ajax.runAJAX('call=resume&utf8='+ajax_spell.utf8ok+
                   '&data='+encodeURIComponent(text));
    }
  };

}

// create the global object
ajax_spell = new ajax_spell_class();

//Setup VIM: ex: et ts=2 enc=utf-8 :
