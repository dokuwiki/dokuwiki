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

/**
 * Get the X offset of the top left corner of the given object
 *
 * @author Garrison Locke <http://www.broken-notebook.com>
 */
function findPosX(object){
  var curleft = 0;
  var obj = document.getElementById(object);
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
 * @author Garrison Locke <http://www.broken-notebook.com>
 */
function findPosY(object){
  var curtop = 0;
  var obj = document.getElementById(object);
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
 * quotes single quotes
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function qquote(str){
  return str.split('\'').join('\\\'');
}

/**
 * This function generates a spellchecker button with localized tooltips
 */
function spellButton(imageFile, speedTip, funcCall, accessKey) {
  speedTip=escapeQuotes(speedTip);
  funcCall=escapeQuotes(funcCall);

  button = "<a ";
  if(accessKey){
    button = button+"accesskey=\""+accessKey+"\" ";
    speedTip = speedTip+' [ALT+'+accessKey.toUpperCase()+']';
  }
  if(funcCall){
    button = button+"href=\"javascript:"+funcCall+";\"";
  }
  button = button+">";
  button = button+"<img width=\"24\" height=\"24\" src=\""+
                DOKU_BASE+'lib/images/toolbar/'+imageFile+"\" border=\"0\" alt=\""+
                speedTip+"\" title=\""+speedTip+"\">";
  button = button+"</a>";
  return button;
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
  this.handler = DOKU_BASE+'lib/exe/spellcheck.php';
  // to hold the page objects (initialized with init())
  this.textboxObj = null;
  this.showboxObj = null;
  this.suggestObj = null;
  this.actionObj  = null;
  this.editbarObj = null;
  // hold translations
  this.txtStart = 'Check Spelling';
  this.txtStop  = 'Resume Editing';
  this.txtRun   = 'Checking...';
  this.txtNoErr = 'No Mistakes';
  this.txtNoSug = 'No Suggestions';
  this.txtChange= 'Change';


  /**
   * Initializes everything
   *
   * Call after the page was setup. Hardcoded element IDs here.
   *
   * @author Andreas Gohr <andi@splitbrain.org>
   */
  this.init = function(txtStart,txtStop,txtRun,txtNoErr,txtNoSug,txtChange){
     // don't run twice
    if (this.inited) return;
    this.inited = true;

    // check for AJAX availability
    var ajax = new sack();
    if(ajax.failed) return;

    // get Elements
    this.textboxObj = document.getElementById('wikitext');
    this.editbarObj = document.getElementById('wikieditbar');
    this.showboxObj = document.getElementById('spell_result'); 
    this.suggestObj = document.getElementById('spell_suggest');
    this.actionObj  = document.getElementById('spell_action'); 

    // set Translation Strings
    this.txtStart = txtStart;
    this.txtStop  = txtStop;
    this.txtRun   = txtRun;
    this.txtNoErr = txtNoErr; 
    this.txtNoSug = txtNoSug;
    this.txtChange= txtChange;

    // register click event
    document.onclick = this.docClick;

    // register focus event
    this.textboxObj.onfocus = this.setState;

    this.setState('start');    
  }

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
  }

  /**
   * Changes the Spellchecker link according to the given mode
   *
   * @author Andreas Gohr <andi@splitbrain.org>
   */
  this.setState = function(state){
    switch (state){
      case 'stop':
        ajax_spell.actionObj.innerHTML = spellButton("spellstop.png",ajax_spell.txtStop,"ajax_spell.resume()","");
        break;
      case 'noerr':
        ajax_spell.actionObj.innerHTML = spellButton("spellnoerr.png",ajax_spell.txtNoErr,"ajax_spell.setState(\"start\")","");
        break;
      case 'run':
        ajax_spell.actionObj.innerHTML = spellButton("spellwait.gif",ajax_spell.txtRun,"","");
        break;
      default:
        ajax_spell.actionObj.innerHTML = spellButton("spellcheck.png",ajax_spell.txtStart,"ajax_spell.run()","c");
        break;
    }
  }

  /**
   * Replaces a word identified by id with its correction given in word
   *
   * @author Garrison Locke <http://www.broken-notebook.com>
   */
  this.correct = function (id, word){
    var obj = document.getElementById('spell_error'+id);
    obj.innerHTML = decodeURIComponent(word);
    obj.style.color = "#005500";
    this.suggestObj.style.display = "none";
  } 

  /**
   * Opens a prompt to let the user change the word her self
   * 
   * @author Andreas Gohr <andi@splitbrain.org>
   */
  this.ask = function(id){
    var word = document.getElementById('spell_error'+id).innerHTML;
    word = prompt(this.txtChange,word);
    if(word){
      this.correct(id,encodeURIComponent(word));
    }
  }

  /**
   * Displays the suggestions for a misspelled word
   *
   * @author Andreas Gohr <andi@splitbrain.org>
   * @author Garrison Locke <http://www.broken-notebook.com>
   */
  this.suggest = function(){
    var args = this.suggest.arguments;
    if(!args[0]) return;
    var id   = args[0];

    // set position of the popup
    this.suggestObj.style.display = "none";
    var x = findPosX('spell_error'+id);
    var y = findPosY('spell_error'+id);

    // handle scrolling 
    if(is_opera){
      var scrollPos = 0; //FIXME how to do this without browser sniffing?
    }else{
      var scrollPos = this.showboxObj.scrollTop;
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
  }

  // --- Callbacks ---

  /**
   * Callback. Called after finishing spellcheck.
   * Inside the callback 'this' is the SACK object!!
   *
   * @author Andreas Gohr <andi@splitbrain.org>
   */ 
  this.start = function(){
    var data  = this.response;
    var error = data.charAt(0);
        data  = data.substring(1);
    if(error == '1'){
      ajax_spell.setState('stop');
      // convert numeric entities back to UTF-8
      data = data.replace(/&#(\d+);/g,
                          function(whole,match1) {
                            return String.fromCharCode(+match1);
                          });
      // replace textbox through div
      ajax_spell.showboxObj.innerHTML     = data;
      ajax_spell.showboxObj.style.width   = ajax_spell.textboxObj.style.width;
      ajax_spell.showboxObj.style.height  = ajax_spell.textboxObj.style.height;
      ajax_spell.textboxObj.style.display = 'none';
      ajax_spell.editbarObj.style.visibility = 'hidden';
      ajax_spell.showboxObj.style.display = 'block';
    }else{
      if(error == '2'){
        alert(data);
      }
      ajax_spell.textboxObj.disabled = false;
      ajax_spell.editbarObj.style.visibility = 'visible';
      ajax_spell.setState('noerr');
    }
  }

  /**
   * Callback. Gets called by resume() - switches back to edit mode
   * Inside the callback 'this' is the SACK object!!
   *
   * @author Andreas Gohr <andi@splitbrain.org>
   */
  this.stop = function(){
    var data = this.response;

    // convert numeric entities back to UTF-8
    data = data.replace(/&#(\d+);/g,
                          function(whole,match1) {
                            return String.fromCharCode(+match1);
                        });
    // now remove &amp; protection
    data = data.replace(/&amp;/g,'&'); 

    ajax_spell.setState('start');
    // replace div with textbox again
    ajax_spell.textboxObj.value         = data;
    ajax_spell.textboxObj.disabled      = false;
    ajax_spell.showboxObj.style.display = 'none';
    ajax_spell.textboxObj.style.display = 'block';
    ajax_spell.editbarObj.style.visibility = 'visible';
    ajax_spell.showboxObj.innerHTML     = '';
  }

  // --- Callers ---

  /**
   * Starts the spellchecking by sending an AJAX request
   *
   * @author Andreas Gohr <andi@splitbrain.org>
   */
  this.run = function(){
    this.setState('run');
    this.textboxObj.disabled = true;
    var ajax = new sack(this.handler);
    ajax.AjaxFailedAlert = '';
    ajax.encodeURIString = false;
    ajax.onCompletion    = this.start;
    ajax.runAJAX('call=check&data='+encodeURIComponent(this.textboxObj.value));
  }

  /**
   * Rewrites the HTML back to text again using an AJAX request
   *
   * @author Andreas Gohr <andi@splitbrain.org>
   */
  this.resume = function(){
    this.setState('run');
    var text = this.showboxObj.innerHTML;
    if(text != ''){
      var ajax = new sack(this.handler);
      ajax.AjaxFailedAlert = '';
      ajax.encodeURIString = false;
      ajax.onCompletion    = this.stop;
      ajax.runAJAX('call=resume&data='+encodeURIComponent(text));
    }
  }

}

// create the global object
ajax_spell = new ajax_spell_class();

//Setup VIM: ex: et ts=2 enc=utf-8 :
