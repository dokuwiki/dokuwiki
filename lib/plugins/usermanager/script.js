/**
 * Add JavaScript confirmation to the User Delete button
 */
jQuery(function(){
    jQuery('#usrmgr__del').on('click', function(){
        return confirm(LANG.del_confirm);
    });
});
