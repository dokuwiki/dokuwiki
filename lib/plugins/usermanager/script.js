/**
 * Add JavaScript confirmation to the User Delete button
 */
jQuery(function(){
    jQuery('#usrmgr__del').click(function(){
        return confirm(LANG.del_confirm);
    });
});
