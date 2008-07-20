/**
 * Add JavaScript confirmation to the User Delete button
 */
function usrmgr_delconfirm(){
    if($('usrmgr__del')){
        addEvent( $('usrmgr__del'),'click',function(){ return confirm(reallyDel); } );
    }
};
addInitEvent(usrmgr_delconfirm);
