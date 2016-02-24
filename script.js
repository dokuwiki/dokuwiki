jQuery(function(){
    'use strict';

    /**
     * Attach datepicker to date types
     */
    jQuery('input.struct_date').datepicker({
        dateFormat: 'yy-mm-dd'
    });

    /**
     * Attach image dialog to image types
     */
    jQuery('button.struct_img').click(function (event) {
        var input_id = event.target.id.substr(0,event.target.id.length-'Button'.length);
        window.open(
            DOKU_BASE+"lib/exe/mediamanager.php?ns=:"+'&edid='+encodeURIComponent(input_id) + '&onselect=insertStructImage',
            'mediaselect',
            'width=750,height=500,left=20,top=20,scrollbars=yes,resizable=yes'); //
    });

    /**
     * Custom onSelect handler for struct img button
     *
     * @param edid
     * @param mediaid
     * @param opts
     * @param align
     */
    window.insertStructImage = function(edid, mediaid, opts, align) {
        jQuery('#'+edid).val(mediaid).change();
    };

    /**
     * Duplicate the elements in .newtemplate whenever any input in it changes
     */
    jQuery('#dw__editform').find('.struct .newtemplate').each(function(){
        var $tplwrapper = jQuery(this);
        var tpl = $tplwrapper.html();
        $tplwrapper.on('change', 'input,textarea,select', function(){
            if(jQuery(this).val() == '') return;
            $tplwrapper.append(tpl);
        });
    });
});
