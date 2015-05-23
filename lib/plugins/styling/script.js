/* DOKUWIKI:include_once iris.js */

jQuery(function () {
    // user openend the admin page, set cookie and redirect
    if (jQuery('#plugin__styling').length) {
        DokuCookie.setValue('styling_plugin', 1);
        document.location.href = document.location.href.replace(/&?do=admin/, '');
    }

    // continue only if the styling Dialog is currently enabled
    if (DokuCookie.getValue('styling_plugin') != 1) return;

    var styling_timeout = null;

    // create dialog element
    var $dialog = jQuery(document.createElement('div'));
    jQuery('body').append($dialog);


    /**
     * updates the current CSS with a new preview one
     */
    function styling_updateCSS() {
        var now = new Date().getTime();
        var $style = jQuery('link[rel=stylesheet][href*="lib/exe/css.php"]');
        $style.attr('href', DOKU_BASE + 'lib/exe/css.php?preview=1&tseed=' + now);
    }

    /**
     * save current values and reload preview (debounced)
     */
    function styling_saveAndUpdate() {
        if (styling_timeout) window.clearTimeout(styling_timeout);
        styling_timeout = window.setTimeout(function () {
            styling_timeout = null;

            var params = $dialog.find('input[type=text]').serializeArray();
            params[params.length] = { name: 'call', value: 'plugin_styling'};
            params[params.length] = {name: 'run', value: 'preview'};

            jQuery.post(
                    DOKU_BASE + 'lib/exe/ajax.php',
                params,
                styling_updateCSS
            );
        }, 500);
    }

    // load the dialog content and apply listeners
    $dialog.load(
            DOKU_BASE + 'lib/exe/ajax.php',
        {
            'call': 'plugin_styling',
            'run':  'html',
            'id':   JSINFO.id
        },
        function () {
            // load the preview template
            styling_updateCSS();

            // open the dialog
            $dialog.dialog({
                'title':    LANG.plugins.styling.menu,
                'width':    500,
                'height':   500,
                'top':      50,
                'position': { 'my': 'left bottom', 'at': 'left bottom', 'of': window },
                // bring everything back to normal
                'close':    function (event, ui) {
                    // disable the styling plugin again
                    DokuCookie.setValue('styling_plugin', 0);
                    // reload
                    document.location.reload()
                }
            });

            // we don't need the manual preview in JS mode
            $dialog.find('.btn_preview').hide();

            // add the color picker  FIXME add saveAndUpdate to correct event
            $dialog.find('.color').iris({ });

            // listen to keyup events
            $dialog.find('input[type=text]').keyup(function () {
                console.log('change');
                styling_saveAndUpdate();
            });

        }
    );

});
