/* DOKUWIKI:include_once iris.js */

jQuery(function () {
    // user openend the admin page, set cookie and redirect
    if (jQuery('#plugin__styler').length) {
        DokuCookie.setValue('styler_plugin', 1);
        document.location.href = document.location.href.replace(/do=admin/, '');
    }

    // continue only if the Styler Dialog is currently enabled
    if (DokuCookie.getValue('styler_plugin') != 1) return;

    var styler_timeout = null;

    // create dialog element
    var $dialog = jQuery(document.createElement('div'));
    jQuery('body').append($dialog);


    /**
     * updates the current CSS with a new preview one
     */
    function styler_updateCSS() {
        var now = new Date().getTime();
        var $style = jQuery('link[rel=stylesheet][href*="lib/exe/css.php"]');
        $style.attr('href', DOKU_BASE + 'lib/exe/css.php?preview=1&tseed=' + now);
    }

    /**
     * save current values and reload preview (debounced)
     */
    function styler_saveAndUpdate() {
        if (styler_timeout) window.clearTimeout(styler_timeout);
        styler_timeout = window.setTimeout(function () {
            styler_timeout = null;

            var params = $dialog.find('input[type=text]').serializeArray();
            params[params.length] = { name: 'call', value: 'plugin_styler'};
            params[params.length] = {name: 'run', value: 'preview'};

            jQuery.post(
                    DOKU_BASE + '/lib/exe/ajax.php',
                params,
                styler_updateCSS
            );
        }, 500);
    }

    // load the dialog content and apply listeners
    $dialog.load(
            DOKU_BASE + '/lib/exe/ajax.php',
        {
            'call': 'plugin_styler',
            'run':  'html',
            'id':   JSINFO.id
        },
        function () {
            // load the preview template
            styler_updateCSS();

            // open the dialog
            $dialog.dialog({
                'title':    LANG.plugins.styler.menu,
                'width':    500,
                'height':   500,
                'top':      50,
                'position': { 'my': 'left bottom', 'at': 'left bottom', 'of': window },
                // bring everything back to normal
                'close':    function (event, ui) {
                    // disable the styler plugin again
                    DokuCookie.setValue('styler_plugin', 0);
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
                styler_saveAndUpdate();
            });

        }
    );

});