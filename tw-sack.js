// ====== Simple Ajax Code Kit =======
// code by Gregory Wild-Smith (c)2005
// http://twilightuniverse.com
// If you use this code please keep this credit intact.
// A link or email would be nice, but is not required.
// v1.01

function sack(file){
  this.AjaxFailedAlert = "Your browser doesn't support the extended functionality of this website, and therefore you have have an experience that differs from the intended one.\n";
  this.requestFile = file;
  this.method = "POST";
  this.URLString = "";
  this.encodeURIString = true;
  this.execute = false;

  this.onLoading = function() { };
  this.onLoaded = function() { };
  this.onInteractive = function() { };
  this.onCompletion = function() { };
  
  this.createAJAX = function() {
    try {
      this.xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
    } catch (e) {
      try {
        this.xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
      } catch (oc) {
        this.xmlhttp = null;
      }
    }
    if(!this.xmlhttp && typeof XMLHttpRequest != "undefined")
      this.xmlhttp = new XMLHttpRequest();
    if (!this.xmlhttp){
      // no XMLHttpRequest support, so no AJAX.
      this.failed = true; 
    }
  };

  this.encodeURLString = function(string){
    varArray = string.split('&');
    for (i = 0; i < varArray.length; i++){
      urlVars = varArray[i].split('=');
      if (urlVars[0].indexOf('amp;') != -1){
        urlVars[0] = urlVars[0].substring(4);
      }
      urlVars[0] = encodeURIComponent(urlVars[0]);
      urlVars[1] = encodeURIComponent(urlVars[1]);
      varArray[i] = urlVars.join("=");
    }
  return varArray.join('&');
  }
  
  this.runResponse = function(){
    eval(this.response);
  }
  
  this.runAJAX = function(urlstring){
    if (urlstring){ this.URLString = urlstring; }
    if (this.element) { this.elementObj = document.getElementById(this.element); }
    if (this.xmlhttp) {
      var self = this; // wierd fix for odd behavior where "this" wouldn't work in the readystate function.
      // Opera doesn't support setRequestHeader, try catch is for IE
      try {
        if(!this.xmlhttp.setRequestHeader){
          this.method = "GET";
        }
      } catch (ex) { }

      if (this.encodeURIString){ this.URLString = this.encodeURLString(this.URLString); }

      if (this.method == "POST") {
        this.xmlhttp.open(this.method, this.requestFile ,true);
        this.xmlhttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        this.xmlhttp.send(this.URLString);
      }else{
        this.xmlhttp.open(this.method, this.requestFile+'?'+this.URLString, true);
        this.xmlhttp.send();
      }

      this.xmlhttp.onreadystatechange = function() {
        switch (self.xmlhttp.readyState){
          case 1: // Loading.
            self.onLoading();
          break;
          case 2: // Loaded.
            self.onLoaded();
          break;
          case 3: // Interactive - is called every 4096 bytes.. pretty much just tells you it's downloading data.
            self.onInteractive();
          break;
          case 4: // Completed.
            self.response = self.xmlhttp.responseText;
             self.responseXML = self.xmlhttp.responseXML;
            self.onCompletion();
            if(self.execute){ self.runResponse(); }
            if (self.elementObj) { 
              self.elementObj.innerHTML = self.response;
            }
          break;
        }
      };
    }
  };
this.createAJAX();
//if(this.failed && this.AjaxFailedAlert){ alert(this.AjaxFailedAlert); }
}
//Setup VIM: ex: et ts=2 enc=utf-8 :
