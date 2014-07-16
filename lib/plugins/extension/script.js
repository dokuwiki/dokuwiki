jQuery(function(){

    var $extmgr = jQuery('#extension__manager');

    /**
     * Confirm uninstalling
     */
    $extmgr.find('input.uninstall').click(function(e){
        if(!window.confirm(LANG.plugins.extension.reallydel)){
            e.preventDefault();
            return false;
        }
        return true;
    });

    /**
     * very simple lightbox
     * @link http://webdesign.tutsplus.com/tutorials/htmlcss-tutorials/super-simple-lightbox-with-css-and-jquery/
     */
    $extmgr.find('a.extension_screenshot').click(function(e) {
        e.preventDefault();

        //Get clicked link href
        var image_href = jQuery(this).attr("href");

        // create lightbox if needed
        var $lightbox = jQuery('#plugin__extensionlightbox');
        if(!$lightbox.length){
            $lightbox = jQuery('<div id="plugin__extensionlightbox"><p>Click to close</p><div></div></div>')
                .appendTo(jQuery('body'))
                .hide()
                .click(function(){
                    $lightbox.hide();
                });
        }

        // fill and show it
        $lightbox
            .show()
            .find('div').html('<img src="' + image_href + '" />');


        return false;
    });

    /**
     * Enable/Disable extension via AJAX
     */
    $extmgr.find('input.disable, input.enable').click(function (e) {
        e.preventDefault();
        var $btn = jQuery(this);

        // get current state
        var extension = $btn.attr('name').split('[')[2];
        extension = extension.substr(0, extension.length - 1);
        var act = ($btn.hasClass('disable')) ? 'disable' : 'enable';

        // disable while we wait
        $btn.attr('disabled', 'disabled');
        $btn.css('cursor', 'wait');

        // execute
        jQuery.get(
            DOKU_BASE + 'lib/exe/ajax.php',
            {
                call: 'plugin_extension',
                ext:  extension,
                act:  act
            },
            function (data) {
                $btn.css('cursor', '')
                    .removeAttr('disabled')
                    .removeClass('disable')
                    .removeClass('enable')
                    .val(data.label)
                    .addClass(data.reverse)
                .parents('li')
                    .removeClass('disabled')
                    .removeClass('enabled')
                    .addClass(data.state);
            }
        );
    });

    /**
     * AJAX detail infos
     */
    $extmgr.find('a.info').click(function(e){
        e.preventDefault();

        var $link = jQuery(this);
        var $details = $link.parent().find('dl.details');
        if($details.length){
            $link.toggleClass('close');
            $details.toggle();
            return;
        }

        $link.addClass('close');
        jQuery.get(
            DOKU_BASE + 'lib/exe/ajax.php',
            {
                call: 'plugin_extension',
                ext: $link.data('extid'),
                act: 'info'
            },
            function(data){
                $link.parent().append(data);
            }
        );
    });

});