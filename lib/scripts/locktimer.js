/**
 * Class managing the timer to display a warning on a expiring lock
 */
var dw_locktimer = {
    timeout: 0,
    draft: false,
    timerID: null,
    lasttime: null,
    msg: LANG.willexpire,
    pageid: '',

    /**
     * Initialize the lock timer
     *
     * @param int    timeout Length of timeout in seconds
     * @param string msg     Deprecated; The expiry message
     * @param bool   draft   Whether to save drafts
     * @param string edid    Optional; ID of an edit object which has to be present
     */
    init: function(timeout,msg,draft,edid){
        var $edit;

        switch (arguments.length) {
        case 4:
            DEPRECATED('Setting the locktimer expiry message is deprecated');
            dw_locktimer.msg = msg;
            break;
        case 3:
            edid = draft;
        case 2:
            draft = msg;
        }
        edid = edid || 'wiki__text';

        $edit = jQuery('#' + edid);
        if($edit.length === 0 || $edit.attr('readonly')) {
            return;
        }

        // init values
        dw_locktimer.timeout  = timeout*1000;
        dw_locktimer.draft    = draft;
        dw_locktimer.lasttime = new Date();

        dw_locktimer.pageid   = jQuery('#dw__editform input[name=id]').val();
        if(!dw_locktimer.pageid) {
            return;
        }

        // register refresh event
        $edit.keypress(dw_locktimer.refresh);
        // start timer
        dw_locktimer.reset();
    },

    /**
     * (Re)start the warning timer
     */
    reset: function(){
        dw_locktimer.clear();
        dw_locktimer.timerID = window.setTimeout(dw_locktimer.warning, dw_locktimer.timeout);
    },

    /**
     * Display the warning about the expiring lock
     */
    warning: function(){
        dw_locktimer.clear();
        alert(fixtxt(dw_locktimer.msg));
    },

    /**
     * Remove the current warning timer
     */
    clear: function(){
        if(dw_locktimer.timerID !== null){
            window.clearTimeout(dw_locktimer.timerID);
            dw_locktimer.timerID = null;
        }
    },

    /**
     * Refresh the lock via AJAX
     *
     * Called on keypresses in the edit area
     */
    refresh: function(){
        var now = new Date(),
            params = 'call=lock&id=' + dw_locktimer.pageid + '&';

        // refresh every minute only
        if(now.getTime() - dw_locktimer.lasttime.getTime() <= 30*1000) {
            return;
        }

        // POST everything necessary for draft saving
        if(dw_locktimer.draft && jQuery('#dw__editform textarea[name=wikitext]').length > 0){
            params += jQuery('#dw__editform').find('input[name=prefix], ' +
                                                   'textarea[name=wikitext], ' +
                                                   'input[name=suffix], ' +
                                                   'input[name=date]').serialize();
        }

        jQuery.post(
            DOKU_BASE + 'lib/exe/ajax.php',
            params,
            dw_locktimer.refreshed,
            'html'
        );
        dw_locktimer.lasttime = now;
    },

    /**
     * Callback. Resets the warning timer
     */
    refreshed: function(data){
        var error = data.charAt(0);
        data = data.substring(1);

        jQuery('#draft__status').html(data);
        if(error != '1') {
            return; // locking failed
        }
        dw_locktimer.reset();
    }
};
