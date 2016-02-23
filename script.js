jQuery(function(){
    'use strict';

    jQuery('input.struct_date').datepicker({
        dateFormat: 'yy-mm-dd'
    });

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
