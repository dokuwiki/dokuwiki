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
    fieldsToSaveAsDraft: [
        'input[name=prefix]',
        'textarea[name=wikitext]',
        'input[name=suffix]',
        'input[name=date]',
    ],
    callbacks: [],

    /**
     * Initialize the lock timer
     *
     * @param {int}    timeout Length of timeout in seconds
     * @param {bool}   draft   Whether to save drafts
     * @param {string} edid    Optional; ID of an edit object which has to be present
     */
    init: function(timeout,draft,edid){
        var $edit;

        edid = edid || 'wiki__text';

        $edit = jQuery('#' + edid);
        if($edit.length === 0 || $edit.attr('readonly')) {
            return;
        }

        // init values
        dw_locktimer.timeout  = timeout*1000;
        dw_locktimer.draft    = draft;
        dw_locktimer.lasttime = new Date();

        dw_locktimer.pageid   = jQuery('#dw__editform').find('input[name=id]').val();
        if(!dw_locktimer.pageid) {
            return;
        }

        // register refresh event
        $edit.keypress(dw_locktimer.refresh);
        // start timer
        dw_locktimer.reset();
    },

    /**
     * Add another field of the editform to be posted to the server when a draft is saved
     */
    addField: function(selector) {
        dw_locktimer.fieldsToSaveAsDraft.push(selector);
    },

    /**
     * Add a callback that is executed when the post request to renew the lock and save the draft returns successfully
     *
     * If the user types into the edit-area, then dw_locktimer will regularly send a post request to the DokuWiki server
     * to extend the page's lock and update the draft. When this request returns successfully, then the draft__status
     * is updated. This method can be used to add further callbacks to be executed at that moment.
     *
     * @param {function} callback the only param is the data returned by the server
     */
    addRefreshCallback: function(callback) {
        dw_locktimer.callbacks.push(callback);
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

        // refresh every half minute only
        if(now.getTime() - dw_locktimer.lasttime.getTime() <= 30*1000) {
            return;
        }

        // POST everything necessary for draft saving
        if(dw_locktimer.draft && jQuery('#dw__editform').find('textarea[name=wikitext]').length > 0){
            params += jQuery('#dw__editform').find(dw_locktimer.fieldsToSaveAsDraft.join(', ')).serialize();
        }

        jQuery.post(
            DOKU_BASE + 'lib/exe/ajax.php',
            params,
            null,
            'json'
        ).done(function dwLocktimerRefreshDoneHandler(data) {
            dw_locktimer.callbacks.forEach(
                function (callback) {
                    callback(data);
                }
            );
        });
        dw_locktimer.lasttime = now;
    },

    /**
     * Callback. Resets the warning timer
     */
    refreshed: function(data){
        if (data.errors.length) {
            data.errors.forEach(function(error) {
                jQuery('#draft__status').after(
                    jQuery('<div class="error"></div>').text(error)
                );
            })
        }

        jQuery('#draft__status').html(data.draft);
        if(data.lock !== '1') {
            return; // locking failed
        }
        dw_locktimer.reset();
    }
};
dw_locktimer.callbacks.push(dw_locktimer.refreshed);
