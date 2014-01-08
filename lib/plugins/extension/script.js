jQuery(function(){

    /**
     * Confirm uninstalling
     */
    jQuery('#extension__manager input.uninstall').click(function(e){
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
    jQuery('#extension__manager a.extension_screenshot').click(function(e) {
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
     * AJAX detail infos
     */
    jQuery('#extension__manager a.info').click(function(e){
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
                ext: $link.data('extid')
            },
            function(data){
                $link.parent().append(data);
            }
        );
    });

});