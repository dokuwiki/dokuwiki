/**
 * Class managing the timer to display a warning on a expiring lock
 */
var locktimer = {
    sack:     null,
    timeout:  0,
    timerID:  null,
    lasttime: null,
    msg:      '',
    pageid:   '',

    init: function(timeout,msg,draft,edid){
        var edit = $(edid);
        if(!edit) return;
        if(edit.readOnly) return;

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
    },

    /**
     * (Re)start the warning timer
     */
    reset: function(){
        locktimer.clear();
        locktimer.timerID = window.setTimeout("locktimer.warning()", locktimer.timeout);
    },

    /**
     * Display the warning about the expiring lock
     */
    warning: function(){
        locktimer.clear();
        alert(locktimer.msg);
    },

    /**
     * Remove the current warning timer
     */
    clear: function(){
        if(locktimer.timerID !== null){
            window.clearTimeout(locktimer.timerID);
            locktimer.timerID = null;
        }
    },

    /**
     * Refresh the lock via AJAX
     *
     * Called on keypresses in the edit area
     */
    refresh: function(){
        var now = new Date();
        // refresh every minute only
        if(now.getTime() - locktimer.lasttime.getTime() > 30*1000){
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
    },

    /**
     * Callback. Resets the warning timer
     */
    refreshed: function(){
        var data  = this.response;
        var error = data.charAt(0);
            data  = data.substring(1);

        $('draft__status').innerHTML=data;
        if(error != '1') return; // locking failed
        locktimer.reset();
    }
};

