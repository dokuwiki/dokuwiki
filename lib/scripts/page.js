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
        dw_page.currentIDHighlight();
        jQuery('a.fn_top').on('mouseover', dw_page.footnoteDisplay);
        dw_page.makeToggle('#dw__toc h3','#dw__toc > div');
    },

    /**
     * Highlight the section when hovering over the appropriate section edit button
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    sectionHighlight: function() {
        jQuery('form.btn_secedit')
            /*
             * wrap the editable section in a div
             */
            .each(function () {
                let $tgt = jQuery(this).parent();
                const match = $tgt.attr('class').match(/(\s+|^)editbutton_(\d+)(\s+|$)/);
                if(!match) return;
                const nr = match[2];
                let $highlight = jQuery(); // holder for elements in the section to be highlighted
                const $highlightWrap = jQuery('<div class="section_highlight_wrapper"></div>');

                // the edit button should be part of the highlight
                $highlight = $highlight.add($tgt);

                // Walk the dom tree in reverse to find the sibling which is or contains the section edit marker
                while ($tgt.length > 0 && !($tgt.hasClass('sectionedit' + nr) || $tgt.find('.sectionedit' + nr).length)) {
                    $tgt = $tgt.prev();
                    $highlight = $highlight.add($tgt);
                }
                // wrap the elements to be highlighted in the section highlight wrapper
                $highlight.wrapAll($highlightWrap);
            })
            /*
             * highlight the section
             */
            .on('mouseover', function () {
                jQuery(this).parents('.section_highlight_wrapper').addClass('section_highlight');
            })
            /*
             * remove highlight
             */
            .on('mouseout', function () {
                jQuery(this).parents('.section_highlight_wrapper').removeClass('section_highlight');
            });
    },


    /**
     * Highlight internal link pointing to current page
     *
     * @author Henry Pan <dokuwiki@phy25.com>
     */
    currentIDHighlight: function(){
        jQuery('a.wikilink1, a.wikilink2').filter('[data-wiki-id="'+JSINFO.id+'"]').wrap('<span class="curid"></span>');
    },

    /**
     * Create/get a insitu popup used by the footnotes
     *
     * @param target - the DOM element at which the popup should be aligned at
     * @param popup_id - the ID of the (new) DOM popup
     * @return the Popup jQuery object
     */
    insituPopup: function(target, popup_id) {
        // get or create the popup div
        var $fndiv = jQuery('#' + popup_id);

        // popup doesn't exist, yet -> create it
        if($fndiv.length === 0){
            $fndiv = jQuery(document.createElement('div'))
                .attr('id', popup_id)
                .addClass('insitu-footnote JSpopup')
                .attr('aria-hidden', 'true')
                .on('mouseleave', function () {jQuery(this).hide().attr('aria-hidden', 'true');})
                .attr('role', 'tooltip');
            jQuery('.dokuwiki:first').append($fndiv);
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
     * @author Anika Henke <anika@selfthinker.org>
     */
    footnoteDisplay: function () {
        var $content = jQuery(jQuery(this).attr('href')) // Footnote text anchor
                      .parent().siblings('.content').clone();

        if (!$content.length) {
            return;
        }

        // prefix ids on any elements with "insitu__" to ensure they remain unique
        jQuery('[id]', $content).each(function(){
            var id = jQuery(this).attr('id');
            jQuery(this).attr('id', 'insitu__' + id);
        });

        var content = $content.html().trim();
        // now put the content into the wrapper
        dw_page.insituPopup(this, 'insitu__fn').html(content)
        .show().attr('aria-hidden', 'false');
    },

    /**
     * Makes an element foldable by clicking its handle
     *
     * This is used for the TOC toggling, but can be used for other elements
     * as well. A state indicator is inserted into the handle and can be styled
     * by CSS.
     *
     * To properly reserve space for the expanded element, the sliding animation is
     * done on the children of the content. To make that look good and to make sure aria
     * attributes are assigned correctly, it's recommended to make sure that the content
     * element contains a single child element only.
     *
     * @param {selector} handle What should be clicked to toggle
     * @param {selector} content This element will be toggled
     * @param {int} state initial state (-1 = open, 1 = closed)
     */
    makeToggle: function(handle, content, state){
        var $handle, $content, $clicky, $child, setClicky;
        $handle = jQuery(handle);
        if(!$handle.length) return;
        $content = jQuery(content);
        if(!$content.length) return;

        // we animate the children
        $child = $content.children();

        // class/display toggling
        setClicky = function(hiding){
            if(hiding){
                $clicky.html('<span>+</span>');
                $handle.addClass('closed');
                $handle.removeClass('open');
            }else{
                $clicky.html('<span>−</span>');
                $handle.addClass('open');
                $handle.removeClass('closed');
            }
        };

        $handle[0].setState = function(state){
            var hidden;
            if(!state) state = 1;

            // Assert that content instantly takes the whole space
            $content.css('min-height', $content.height()).show();

            // stop any running animation
            $child.stop(true, true);

            // was a state given or do we toggle?
            if(state === -1) {
                hidden = false;
            } else if(state === 1) {
                hidden = true;
            } else {
                hidden = $child.is(':hidden');
            }

            // update the state
            setClicky(!hidden);

            // Start animation and assure that $toc is hidden/visible
            $child.dw_toggle(hidden, function () {
                $content.toggle(hidden);
                $content.attr('aria-expanded', hidden);
                $content.css('min-height',''); // remove min-height again
            }, true);
        };

        // the state indicator
        $clicky = jQuery(document.createElement('strong'));

        // click function
        $handle.css('cursor','pointer')
               .on('click', $handle[0].setState)
               .prepend($clicky);

        // initial state
        $handle[0].setState(state);
    }
};

jQuery(dw_page.init);
