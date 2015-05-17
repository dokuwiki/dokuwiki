/* DOKUWIKI:include_once iris.js */

jQuery(function () {
    // user openend the admin page, set cookie and redirect
    if (jQuery('#plugin__styler').length) {
        DokuCookie.setValue('styler_plugin', 1);
        document.location.href = DOKU_BASE;
    }

    // The Styler Dialog is currently enabled, display it here and apply the preview styles
    if (DokuCookie.getValue('styler_plugin') == 1) {
        // load dialog
        var $dialog = jQuery(document.createElement('div'));
        jQuery('body').append($dialog);
        $dialog.load(
                DOKU_BASE + '/lib/exe/ajax.php',
            {
                'call': 'plugin_styler'
            },
            function () {
                // load the preview template
                var now = new Date().getTime();
                var $style = jQuery('link[rel=stylesheet][href*="lib/exe/css.php"]');
                $style.attr('href', DOKU_BASE + 'lib/exe/css.php?preview=1&tseed=' + now);

                // open the dialog
                var $dlg = $dialog.dialog({
                    'title':    LANG.plugins.styler.menu,
                    'width':    500,
                    'top':      50,
                    'maxHeight': 500,
                    'position': { 'my': 'left bottom', 'at': 'left bottom', 'of': window },
                    // bring everything back to normal
                    'close':    function (event, ui) {
                        // disable the styler plugin again
                        DokuCookie.setValue('styler_plugin', 0);
                        // reload
                        document.location.reload()
                    }
                });

                jQuery('.styler .color').iris({
                });

            }
        );
    }
});