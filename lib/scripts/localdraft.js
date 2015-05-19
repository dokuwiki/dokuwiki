var localdraft = {
    $form: null,
    timer: null,

    init: function () {
        if (!localdraft.supports_html5_storage()) {
            return;
        }

        localdraft.$form = jQuery('#dw__editform');
        if (localdraft.$form.length) {
            // register draft saving
            jQuery('#wiki__text').keyup(function () {
                // save after 500ms iactivity
                if (localdraft.timer) window.clearTimeout(localdraft.timer);
                localdraft.timer = window.setTimeout(localdraft.saveDraft, 500);
            });
        } else {
            localdraft.clearSavedDraft();
            // if a draft still exists it needs to be sent to the server
            localdraft.saveRemoteDraft();
        }
    },

    /**
     * Check if localstorage is available
     *
     * @returns {boolean}
     */
    supports_html5_storage: function () {
        try {
            return 'localStorage' in window && window['localStorage'] !== null;
        } catch (e) {
            return false;
        }
    },

    /**
     * deletes a draft that has been successfully saved already
     *
     * It compares the last save date with the date of the revision the draft was editing.
     * So this behaves similar to the remote drafts in that a draft will be deleted when
     * someone else edited the page meanwhile
     */
    clearSavedDraft: function () {
        var draft_date = window.localStorage.getItem('draft_date-' + JSINFO.id);
        if (draft_date && draft_date < JSINFO['lastmod']) {
            localdraft.removeDraft();
        }
    },

    /**
     * remove local draft of the local page
     */
    removeDraft: function () {
        window.localStorage.removeItem('draft_text-' + JSINFO.id);
        window.localStorage.removeItem('draft_date-' + JSINFO.id);
    },

    /**
     * Save the current page in the local draft
     */
    saveDraft: function () {
        console.log('sving draft');

        var page =
            localdraft.$form.find('input[name=id]').val();

        var state =
            localdraft.$form.find('input[name=prefix]').val() +
            localdraft.$form.find('textarea[name=wikitext]').val() +
            localdraft.$form.find('input[name=suffix]').val();

        var date =
            localdraft.$form.find('input[name=date]').val();

        window.localStorage.setItem('draft_text-' + page, state);
        window.localStorage.setItem('draft_date-' + page, date);

    },

    /**
     * Saves the locally stored draft (if any) to the server
     */
    saveRemoteDraft: function () {
        console.log('getDraft' + JSINFO.id);

        var draft_text = window.localStorage.getItem('draft_text-' + JSINFO.id);
        var draft_date = window.localStorage.getItem('draft_date-' + JSINFO.id);

        if (draft_date && draft_text) {
            var params = {
                'call':     'lock',
                'id':       JSINFO.id,
                'prefix':   '',
                'wikitext': draft_text,
                'suffix':   '',
                'date':     draft_date
            };

            jQuery.post(
                    DOKU_BASE + 'lib/exe/ajax.php',
                params,
                localdraft.onRemoteDraftSave(),
                'html'
            );

        }
    },

    /**
     * Callback when the data has been saved
     * @param data
     */
    onRemoteDraftSave: function (data) {
        localdraft.removeDraft();
        // page's draft status might have changed, reload
        document.location.reload();
    }
};

jQuery(localdraft.init);