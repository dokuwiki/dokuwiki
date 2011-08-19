/**
 * Page behaviours
 *
 * This class adds various behaviours to the rendered page
 */
dw_page = {
    /**
     * initialize page behaviours
     */
    init: function(){
        dw_page.sectionHighlight();
        jQuery('a.fn_top').mouseover(dw_page.footnoteDisplay);
    },

    /**
     * Highlight the section when hovering over the appropriate section edit button
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    sectionHighlight: function() {
        jQuery('form.btn_secedit')
            .mouseover(function(e){
                var tgt = this.parentNode;
                var nr = tgt.className.match(/(\s+|^)editbutton_(\d+)(\s+|$)/)[2];
                do {
                    tgt = tgt.previousSibling;
                } while (tgt !== null && typeof tgt.tagName === 'undefined');
                if (tgt === null) return;
                while(typeof tgt.className === 'undefined' ||
                      tgt.className.match('(\\s+|^)sectionedit' + nr + '(\\s+|$)') === null) {
                    if (typeof tgt.className !== 'undefined') {
                        jQuery(tgt).addClass('section_highlight');
                    }
                    tgt = (tgt.previousSibling !== null) ? tgt.previousSibling : tgt.parentNode;
                }

                jQuery(tgt).addClass('section_highlight');
            })
            .mouseout(function(e){
                jQuery('.section_highlight').removeClass('section_highlight');
            });
    },

    /**
     * Create/get a insitu popup used by the footnotes
     *
     * @param target - the DOM element at which the popup should be aligned at
     * @param popup_id - the ID of the (new) DOM popup
     * @return the Popup JQuery object
     */
    insituPopup: function(target, popup_id) {
        // get or create the popup div
        var $fndiv = jQuery('#popup_id');

        // popup doesn't exist, yet -> create it
        if(!$fndiv.length){
            $fndiv = jQuery(document.createElement('div'))
                .attr('id', popup_id)
                .addClass('insitu-footnote JSpopup')
                .mouseout(function(e){
                    // autoclose on mouseout - ignoring bubbled up events
                    //FIXME can this made simpler in jQuery?
                    var p = e.relatedTarget || e.toElement;
                    while (p && p !== this) {
                        p = p.parentNode;
                    }
                    if (p === this) {
                        return;
                    }
                    jQuery(this).hide();
                });

            jQuery('div.dokuwiki:first').append($fndiv);
        }

        $fndiv.position({
            my: 'left top',
            at: 'left center',
            of: target
        });

        $fndiv.hide();
        return $fndiv;
    },

    /**
     * Display an insitu footnote popup
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     * @author Chris Smith <chris@jalakai.co.uk>
     */
    footnoteDisplay: function(e){
        var $fndiv = dw_page.insituPopup(e.target, 'insitu__fn');

        // locate the footnote anchor element
        var $a = jQuery("#fn__" + e.target.id.substr(5));
        if (!$a.length){ return; }

        // anchor parent is the footnote container, get its innerHTML
        var content = new String ($a.parent().parent().html());

        // strip the leading content anchors and their comma separators
        content = content.replace(/<sup>.*<\/sup>/gi, '');
        content = content.replace(/^\s+(,\s+)+/,'');

        // prefix ids on any elements with "insitu__" to ensure they remain unique
        content = content.replace(/\bid=(['"])([^"']+)\1/gi,'id="insitu__$2');

        // now put the content into the wrapper
        $fndiv.html(content);
        $fndiv.show();
    }
};

jQuery(dw_page.init);
