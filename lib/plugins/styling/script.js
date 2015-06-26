/* DOKUWIKI:include_once iris.js */

jQuery(function () {

    var $styling_plugin = jQuery('#plugin__styling');
    if(!$styling_plugin.length) return;


    if (!$styling_plugin.hasClass('ispopup')) {
        var $hl = $styling_plugin.find('h1').first();
        var $btn = jQuery('<button class="btn">' + LANG.plugins.styling.popup + '</button>');
        $hl.append($btn);

        $btn.click(function (e) {
            var windowFeatures = "menubar=no,location=no,resizable=yes,scrollbars=yes,status=false,width=500,height=500";
            window.open(DOKU_BASE + 'lib/plugins/styling/popup.php', 'styling', windowFeatures)
        });
        return;
    }

    // add the color picker
    $styling_plugin.find('.color').iris({});

    // load preview in main window
    var now = new Date().getTime();
    var $style = window.opener.jQuery('link[rel=stylesheet][href*="lib/exe/css.php"]');
    $style.attr('href', DOKU_BASE + 'lib/exe/css.php?preview=1&tseed=' + now);

});
