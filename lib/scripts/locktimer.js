/**
* Class managing the timer to display a warning on a expiring lock
*/
var locktimer = {
    sack: null,
    timeout: 0,
    timerID: null,
    lasttime: null,
    msg: '',
    pageid: '',

    init: function(timeout,msg,draft){
        // init values
        this.timeout = timeout*1000;
        this.msg = msg;
        this.draft = draft;
        this.lasttime = new Date();

        if(jQuery('#dw__editform').length == 0) return;
        this.pageid = jQuery('#dw__editform input[name=id]').val();
        if(!this.pageid) return;
        
        if(jQuery('#wiki__text').attr('readonly')) return;

        // register refresh event
        jQuery('#dw__editform').keypress(
            function() {
                locktimer.refresh();
            }
        );
        // start timer
        this.reset();
    },

    /**
     * (Re)start the warning timer
     */
    reset: function(){
        this.clear();
        this.timerID = window.setTimeout("locktimer.warning()", this.timeout);
    },

    /**
     * Display the warning about the expiring lock
     */
    warning: function(){
        this.clear();
        alert(this.msg);
    },

    /**
     * Remove the current warning timer
     */
    clear: function(){
        if(this.timerID !== null){
            window.clearTimeout(this.timerID);
            this.timerID = null;
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
        if(now.getTime() - this.lasttime.getTime() > 30*1000){
            params['call'] = 'lock';
            params['id'] = locktimer.pageid;

            if(locktimer.draft && jQuery('#dw__editform textarea[name=wikitext]').length > 0){
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
                function (data) { 	
                    locktimer.refreshed(data);
                },
                'html'
            );
            this.lasttime = now;
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
        this.reset();
    }
};