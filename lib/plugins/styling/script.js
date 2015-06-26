/* DOKUWIKI:include_once iris.js */

jQuery(function () {
    // add popup option to admin page
    var $styling_plugin = jQuery('#plugin__styling');
    if ($styling_plugin.length) {
        var $hl = $styling_plugin.find('h1').first();
        var $btn = jQuery('<button class="btn">' + LANG.plugins.styling.popup + '</button>');
        $hl.append($btn);

        $btn.click(function (e) {
            DokuCookie.setValue('styling_plugin', 1);
            document.location.href = document.location.href.replace(/&?do=admin/, '');
        });
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

    // prepare the dialog
    $dialog.dialog({
        'autoOpen':      false,
        'title':         LANG.plugins.styling.menu,
        'width':         500,
        'height':        500,
        'position':      {'my': 'left bottom', 'at': 'left bottom-40', 'of': window},
        'closeOnEscape': true,

        // bring everything back to normal on close
        'close': function (event, ui) {
            // disable the styling plugin again
            DokuCookie.setValue('styling_plugin', 0);
            // reload
            document.location.reload()
        }
    });


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
            $dialog.dialog('open');

            // add the color picker  FIXME add saveAndUpdate to correct event
            $dialog.find('.color').iris({});
        }
    );

});
