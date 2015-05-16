jQuery(function () {


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
                $dialog.dialog({
                    'title':    'Template Variables',
                    'width':    500,
                    'top':      50,
                    'position': { 'my': 'left top', 'at': 'left top', 'of': window },
                    // bring everything back to normal
                    'close':    function (event, ui) {
                        // disable the styler plugin again
                        DokuCookie.setValue('styler_plugin', 0);
                        // reload
                        document.location.reload()
                    }
                });
            }
        );

    }
});