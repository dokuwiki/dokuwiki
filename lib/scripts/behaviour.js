/*jslint sloppy: true */
/*global jQuery, LANG, document, alert */

/**
 * Automatic behaviours
 *
 * This class wraps various JavaScript functionalities that are triggered
 * automatically whenever a certain object is in the DOM or a certain CSS
 * class was found
 */
var dw_behaviour = {

    init: function(){
        dw_behaviour.focusMarker();
        dw_behaviour.scrollToMarker();
        dw_behaviour.removeHighlightOnClick();
        dw_behaviour.quickSelect();
        dw_behaviour.checkWindowsShares();
        dw_behaviour.initTocToggle();
    },

    /**
     * Looks for an element with the ID scroll__here at scrolls to it
     */
    scrollToMarker: function(){
        var $obj = jQuery('#scroll__here');
        if($obj.length) {
            $obj[0].scrollIntoView();
        }
    },

    /**
     * Looks for an element with the ID focus__this at sets focus to it
     */
    focusMarker: function(){
        jQuery('#focus__this').focus();
    },

    /**
     * Remove all search highlighting when clicking on a highlighted term
     *
     * @FIXME would be nice to have it fade out
     */
    removeHighlightOnClick: function(){
        jQuery('span.search_hit').click(
            function(e){
                jQuery(e.target).removeClass('search_hit');
            }
        );
    },

    /**
     * Autosubmit quick select forms
     *
     * When a <select> tag has the class "quickselect", this script will
     * automatically submit its parent form when the select value changes.
     * It also hides the submit button of the form.
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    quickSelect: function(){
        jQuery('select.quickselect')
            .change(function(e){ e.target.form.submit(); })
            .parents('form').find('input[type=submit]').hide();
    },

    /**
     * Display error for Windows Shares on browsers other than IE
     *
     * @author Michael Klier <chi@chimeric.de>
     */
    checkWindowsShares: function() {
        if(!LANG.nosmblinks || document.all !== null) {
            // No warning requested or none necessary
            return;
        }

        jQuery('a.windows').live('click', function(){
            alert(LANG.nosmblinks);
        });
    },

    /**
     * Adds the toggle switch to the TOC
     */
    initTocToggle: function() {
        var $header = jQuery('#toc__header');
        if(!$header.length) return;
        var $toc    = jQuery('#toc__inside');

        var $clicky = jQuery(document.createElement('span'))
                        .attr('id','toc__toggle')
                        .css('cursor','pointer')
                        .click(function(){
                            $toc.slideToggle(200);
                            setClicky();
                        });
        $header.prepend($clicky);

        var setClicky = function(){
            if($toc.css('display') == 'none'){
                $clicky.html('<span>+</span>');
                $clicky[0].className = 'toc_open';
            }else{
                $clicky.html('<span>&minus;</span>');
                $clicky[0].className = 'toc_close';
            }
        };

        setClicky();
    }

};

/**
 * Hides elements with a slide animation
 *
 * @param fn optional callback to run after hiding
 * @author Adrian Lang <mail@adrianlang.de>
 */
jQuery.fn.dw_hide = function(fn) {
    return this.slideUp('fast', fn);
};

/**
 * Unhides elements with a slide animation
 *
 * @param fn optional callback to run after hiding
 * @author Adrian Lang <mail@adrianlang.de>
 */
jQuery.fn.dw_show = function(fn) {
    return this.slideDown('fast', fn);
};

/**
 * Toggles visibility of an element using a slide element
 *
 * @param bool the current state of the element (optional)
 */
jQuery.fn.dw_toggle = function(bool) {
    return this.each(function() {
        var $this = jQuery(this);
        if (typeof bool === 'undefined') {
            bool = $this.is(':hidden');
        }
        $this[bool ? "dw_show" : "dw_hide" ]();
    });
};

jQuery(dw_behaviour.init);
