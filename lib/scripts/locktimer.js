/**
 * Class managing the timer to display a warning on a expiring lock
 */
var dw_locktimer = {
    timeout: 0,
    draft: false,
    timerID: null,
    lasttime: null,
    msg: '',
    pageid: '',

    /**
     * Initialize the lock timer
     *
     * @param int timeout Lenght of timeout in seconds
     * @param bool draft  save drafts
     */
    init: function(timeout,draft){ //FIXME which elements to pass here?
        var $edit = jQuery('#wiki__text');
        if(!$edit.length) return;
        if($edit.attr('readonly')) return;

        // init values
        dw_locktimer.timeout  = timeout*1000;
        dw_locktimer.draft    = draft;
        dw_locktimer.lasttime = new Date();

        dw_locktimer.pageid   = jQuery('#dw__editform input[name=id]').val();
        if(!dw_locktimer.pageid) return;

        // register refresh event
        jQuery('#wiki__text').keypress(dw_locktimer.refresh);
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
        alert(LANG.willexpire.replace(/\\n/,"\n"));
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
        var now = new Date();
        var params = {};
        // refresh every minute only
        if(now.getTime() - dw_locktimer.lasttime.getTime() > 30*1000){
            params['call'] = 'lock';
            params['id'] = dw_locktimer.pageid;

            if(dw_locktimer.draft && jQuery('#dw__editform textarea[name=wikitext]').length > 0){
                params['prefix'] = jQuery('#dw__editform input[name=prefix]').val();
                params['wikitext'] = jQuery('#dw__editform textarea[name=wikitext]').val();
                params['suffix'] = jQuery('#dw__editform input[name=suffix]').val();
                if(jQuery('#dw__editform input[name=date]').length > 0) {
                    params['date'] = jQuery('#dw__editform input[name=id]').val();
                }
            }

            jQuery.post(
                DOKU_BASE + 'lib/exe/ajax.php',
                params,
                dw_locktimer.refreshed,
                'html'
            );
            dw_locktimer.lasttime = now;
        }
    },

    /**
     * Callback. Resets the warning timer
     */
    refreshed: function(data){
        var error = data.charAt(0);
        data = data.substring(1);

        jQuery('#draft__status').html(data);
        if(error != '1') return; // locking failed
        dw_locktimer.reset();
    }
};
