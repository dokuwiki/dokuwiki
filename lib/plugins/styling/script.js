/* DOKUWIKI:include_once iris.js */

jQuery(function () {

    var $styling_plugin = jQuery('#plugin__styling');
    if (!$styling_plugin.length) return;


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

    // append the loader screen
    $loader = window.opener.jQuery('#plugin__styling_loader');
    if (!$loader.length) {
        $loader = jQuery('<div id="plugin__styling_loader">' + LANG.plugins.styling.loader + '</div>');
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
            'font-size':        '40px',
            'text-align':       'center',
            'line-height':      '90px'
        });
        window.opener.jQuery('body').append($loader);
    }

    // load preview in main window
    var now = new Date().getTime();
    var $style = window.opener.jQuery('link[rel=stylesheet][href*="lib/exe/css.php"]');
    $style.attr('href', '');
    $style.attr('href', DOKU_BASE + 'lib/exe/css.php?preview=1&tseed=' + now);


});
