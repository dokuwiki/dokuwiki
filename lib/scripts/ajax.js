/**
 * AJAX functions for the pagename quicksearch
 *
 * We're using a global object with self referencing methods
 * here to make callbacks work
 *
 * @license  GPL2 (http://www.gnu.org/licenses/gpl.html)
 * @author   Andreas Gohr <andi@splitbrain.org>
 */

//prepare class
function ajax_qsearch_class(){
  this.sack = null;
  this.inObj = null;
  this.outObj = null;
  this.timer = null;
}

//create global object and add functions
var ajax_qsearch = new ajax_qsearch_class();
ajax_qsearch.sack = new sack(DOKU_BASE + 'lib/exe/ajax.php');
ajax_qsearch.sack.AjaxFailedAlert = '';
ajax_qsearch.sack.encodeURIString = false;

ajax_qsearch.init = function(inID,outID){
  ajax_qsearch.inObj  = document.getElementById(inID);
  ajax_qsearch.outObj = document.getElementById(outID);

  // objects found?
  if(ajax_qsearch.inObj === null){ return; }
  if(ajax_qsearch.outObj === null){ return; }

  // attach eventhandler to search field
  addEvent(ajax_qsearch.inObj,'keyup',ajax_qsearch.call);

  // attach eventhandler to output field
  addEvent(ajax_qsearch.outObj,'click',function(){ ajax_qsearch.outObj.style.display='none'; });
};

ajax_qsearch.clear = function(){
  ajax_qsearch.outObj.style.display = 'none';
  ajax_qsearch.outObj.innerHTML = '';
  if(ajax_qsearch.timer !== null){
    window.clearTimeout(ajax_qsearch.timer);
    ajax_qsearch.timer = null;
  }
};

ajax_qsearch.exec = function(){
  ajax_qsearch.clear();
  var value = ajax_qsearch.inObj.value;
  if(value === ''){ return; }
  ajax_qsearch.sack.runAJAX('call=qsearch&q='+encodeURI(value));
};

ajax_qsearch.sack.onCompletion = function(){
  var data = ajax_qsearch.sack.response;
  if(data === ''){ return; }

  ajax_qsearch.outObj.innerHTML = data;
  ajax_qsearch.outObj.style.display = 'block';
};

ajax_qsearch.call = function(){
  ajax_qsearch.clear();
  ajax_qsearch.timer = window.setTimeout("ajax_qsearch.exec()",500);
};

