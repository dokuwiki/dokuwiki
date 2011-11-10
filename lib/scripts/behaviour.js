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
        dw_behaviour.subscription();

        dw_behaviour.revisionBoxHandler();
        jQuery('#page__revisions input[type=checkbox]').click(
            dw_behaviour.revisionBoxHandler
        );
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
            .closest('form').find('input[type=submit]').not('.show').hide();
    },

    /**
     * Display error for Windows Shares on browsers other than IE
     *
     * @author Michael Klier <chi@chimeric.de>
     */
    checkWindowsShares: function() {
        if(!LANG.nosmblinks || typeof(document.all) !== 'undefined') {
            // No warning requested or none necessary
            return;
        }

        jQuery('a.windows').live('click', function(){
            alert(LANG.nosmblinks.replace(/\\n/,"\n"));
        });
    },

    /**
     * Hide list subscription style if target is a page
     *
     * @author Adrian Lang <lang@cosmocode.de>
     * @author Pierre Spring <pierre.spring@caillou.ch>
     */
    subscription: function(){
        var $form, $list, $digest;

        $form = jQuery('#subscribe__form');
        if (0 === $form.length) return;

        $list = $form.find("input[name='sub_style'][value='list']");
        $digest = $form.find("input[name='sub_style'][value='digest']");

        $form.find("input[name='sub_target']")
            .click(
                function () {
                    var $this = jQuery(this), show_list;
                    if (!$this.prop('checked')) {
                        return;
                    }

                    show_list = $this.val().match(/:$/);
                    $list.parent().dw_toggle(show_list);
                    if (!show_list && $list.prop('checked')) {
                        $digest.prop('checked', 'checked');
                    }
                }
            )
            .filter(':checked')
            .click();
    },

    /**
     * disable multiple revisions checkboxes if two are checked
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    revisionBoxHandler: function(){
        var $checked = jQuery('#page__revisions input[type=checkbox]:checked');
        var $all     = jQuery('#page__revisions input[type=checkbox]');

        if($checked.length < 2){
            $all.attr('disabled',false);
            jQuery('#page__revisions input[type=submit]').attr('disabled',true);
        }else{
            $all.attr('disabled',true);
            jQuery('#page__revisions input[type=submit]').attr('disabled',false);
            for(var i=0; i<$checked.length; i++){
                $checked[i].disabled = false;
                if(i>1){
                    $checked[i].checked = false;
                }
            }
        }
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
jQuery.fn.dw_toggle = function(bool, fn) {
    return this.each(function() {
        var $this = jQuery(this);
        if (typeof bool === 'undefined') {
            bool = $this.is(':hidden');
        }
        $this[bool ? "dw_show" : "dw_hide" ](fn);
    });
};

jQuery(dw_behaviour.init);
