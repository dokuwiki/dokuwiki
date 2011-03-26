/**
 * Class managing the timer to display a warning on a expiring lock
 */

// must be global variables, they are called from outside too
var initLocktimer, expWarning;

(function ($) {
    var reset, clear, refresh, refreshed;

    var locktimer = {
        timeout: 0,
        timerID: null,
        lasttime: null,
        msg: '',
        pageid: '',
    };

    initLocktimer = function(timeout, msg, draft){

        // init values
        locktimer.timeout  = timeout*1000;
        locktimer.msg      = msg;
        locktimer.draft    = draft;
        locktimer.lasttime = new Date();

        if($('#dw__editform').length == 0) return;
        locktimer.pageid = $('#dw__editform input[name=id]').val();
        
        if(!locktimer.pageid) return;
        if($('#wiki__text').attr('readonly')) return;

        // register refresh event
        $('#dw__editform').keypress(
            function() {
                refresh();
            }
        );
        
        // start timer
        reset();
    };

    /**
     * (Re)start the warning timer
     */
    reset = function(){
        clear();
        locktimer.timerID = window.setTimeout("expWarning()", locktimer.timeout);
    };

    /**
     * Display the warning about the expiring lock
     */
    expWarning = function(){
        clear();
        alert(locktimer.msg);
    };

    /**
     * Remove the current warning timer
     */
    clear = function(){
        if(locktimer.timerID !== null){
            window.clearTimeout(locktimer.timerID);
            locktimer.timerID = null;
        }
    };

    /**
     * Refresh the lock via AJAX
     *
     * Called on keypresses in the edit area
     */
    refresh = function(){
                         
        var now = new Date();
        var params = {}; 
        
        // refresh every minute only         
        if(now.getTime() - locktimer.lasttime.getTime() > 30*1000){

            params['call'] = 'lock';
            params['id'] = locktimer.pageid;
            
            if(locktimer.draft && $('#dw__editform textarea[name=wikitext]').length > 0){
                params['prefix'] = $('#dw__editform input[name=prefix]').val(); 
                params['wikitext'] = $('#dw__editform textarea[name=wikitext]').val();
                params['suffix'] = $('#dw__editform input[name=suffix]').val();
                
                if($('#dw__editform input[name=date]').length > 0){
                    params['date'] = $('#dw__editform input[name=id]').val();
                }
            }
            
            $.post(
                DOKU_BASE + 'lib/exe/ajax.php',
                params,
                function (data) {
                    refreshed(data);
                },
                'html'
            );
            
            locktimer.lasttime = now;
        }
    };

    /**
     * Callback. Resets the warning timer
     */
    refreshed = function(data){
        var error = data.charAt(0);
        data  = data.substring(1);
            
        $('#draft__status').html(data);
        if(error != '1') return; // locking failed
        reset();
    };
    
}(jQuery));
