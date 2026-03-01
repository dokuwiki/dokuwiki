jQuery(function () {

    /**
     * Function to reload the preview styles in the main window
     *
     * @param {Window} target the main window
     */
    function applyPreview(target) {
        // remove style
        var $style = target.jQuery('link[rel=stylesheet][href*="lib/exe/css.php"]');
        $style.attr('href', '');

        // append the loader screen
        var $loader = target.jQuery('#plugin__styling_loader');
        if (!$loader.length) {
            $loader = target.jQuery('<div id="plugin__styling_loader">' + LANG.plugins.styling.loader + '</div>');
            $loader.css({
                'position':         'absolute',
                'width':            '100%',
                'height':           '100%',
                'top':              0,
                'left':             0,
                'z-index':          5000,
                'background-color': '#fff',
                'opacity':          '0.7',
                'color':            '#000',
                'font-size':        '2.5em',
                'text-align':       'center',
                'line-height':      1.5,
                'padding-top':      '2em'
            });
            target.jQuery('body').append($loader);
        }

        // load preview in main window (timeout works around chrome updating CSS weirdness)
        setTimeout(function () {
            var now = new Date().getTime();
            $style.attr('href', DOKU_BASE + 'lib/exe/css.php?preview=1&tseed=' + now);
        }, 500);
    }

    var doreload = 1;
    var $styling_plugin = jQuery('#plugin__styling');

    // if we are not on the plugin page (either main or popup)
    if (!$styling_plugin.length) {
        // handle the preview cookie
        if(DokuCookie.getValue('styling_plugin') == 1) {
            applyPreview(window);
        }
        return; // nothing more to do here
    }

    /* ---- from here on we're in the popup or admin page ---- */

    // add button on main page
    if (!$styling_plugin.hasClass('ispopup')) {
        var $form = $styling_plugin.find('form.styling').first();
        var $btn = jQuery('<button>' + LANG.plugins.styling.popup + '</button>');
        $form.prepend($btn);

        $btn.on('click', function (e) {
            var windowFeatures = "menubar=no,location=no,resizable=yes,scrollbars=yes,status=false,width=500,height=500";
            window.open(DOKU_BASE + 'lib/plugins/styling/popup.php', 'styling_popup', windowFeatures);
            e.preventDefault();
            e.stopPropagation();
        }).wrap('<p></p>');
        return; // we exit here if this is not the popup
    }

    /* ---- from here on we're in the popup only ---- */

    // reload the main page on close
    window.onunload = function(e) {
        if(doreload) {
            DokuCookie.setValue('styling_plugin', 0);
            if(window.opener) window.opener.document.location.reload();
        }
        return null;
    };

    // don't reload on our own buttons
    jQuery(':button').click(function(e){
        doreload = false;
    });

    // on first load apply preview
    if(window.opener) applyPreview(window.opener);

    // enable the preview cookie
    DokuCookie.setValue('styling_plugin', 1);
});
