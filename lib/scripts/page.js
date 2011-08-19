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
            .mouseover(function(){
                var $tgt = jQuery(this).parent();
                var nr = $tgt.attr('class').match(/(\s+|^)editbutton_(\d+)(\s+|$)/)[2];

                // Walk the DOM tree up (first previous siblings, then parents)
                // until boundary element
                while($tgt.length > 0 && !$tgt.hasClass('sectionedit' + nr)) {
                    // $.last gives the DOM-ordered last element:
                    // prev if present, else parent.
                    $tgt = $tgt.prev().add($tgt.parent()).last();
                    $tgt.addClass('section_highlight');
                }
            })
            .mouseout(function(){
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
        var $fndiv = jQuery('#' + popup_id);

        // popup doesn't exist, yet -> create it
        if($fndiv.length === 0){
            $fndiv = jQuery(document.createElement('div'))
                .attr('id', popup_id)
                .addClass('insitu-footnote JSpopup')
                .mouseleave(function () {jQuery(this).hide();});
            jQuery('div.dokuwiki:first').append($fndiv);
        }

        // position() does not support hidden elements
        $fndiv.show().position({
            my: 'left top',
            at: 'left center',
            of: target
        }).hide();

        return $fndiv;
    },

    /**
     * Display an insitu footnote popup
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     * @author Chris Smith <chris@jalakai.co.uk>
     */
    footnoteDisplay: function(e){
        var $fndiv = dw_page.insituPopup(this, 'insitu__fn');

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
