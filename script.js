jQuery(function(){
    'use strict';

    jQuery('input.struct_date').datepicker({
        dateFormat: 'yy-mm-dd'
    });

    jQuery('button.struct_img').click(function (event) {
        var input_id = event.target.id.substr(0,event.target.id.length-'Button'.length);
        window.open(
            DOKU_BASE+"lib/exe/mediamanager.php?ns=:"+'&edid='+encodeURIComponent(input_id) + '&onselect=insertStructImage',
            'mediaselect',
            'width=750,height=500,left=20,top=20,scrollbars=yes,resizable=yes'); //
    });

    window.insertStructImage = function(edid, mediaid, opts, align) {
        jQuery('#'+edid).val(mediaid).change();
    };
});
