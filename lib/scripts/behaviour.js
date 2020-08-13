/**
 * Hides elements with a slide animation
 *
 * @param {function} fn optional callback to run after hiding
 * @param {bool} noaria supress aria-expanded state setting
 * @author Adrian Lang <mail@adrianlang.de>
 */
jQuery.fn.dw_hide = function(fn, noaria) {
    if(!noaria) this.attr('aria-expanded', 'false');
    return this.slideUp('fast', fn);
};

/**
 * Unhides elements with a slide animation
 *
 * @param {function} fn optional callback to run after hiding
 * @param {bool} noaria supress aria-expanded state setting
 * @author Adrian Lang <mail@adrianlang.de>
 */
jQuery.fn.dw_show = function(fn, noaria) {
    if(!noaria) this.attr('aria-expanded', 'true');
    return this.slideDown('fast', fn);
};

/**
 * Toggles visibility of an element using a slide element
 *
 * @param {bool} state the current state of the element (optional)
 * @param {function} fn callback after the state has been toggled
 * @param {bool} noaria supress aria-expanded state setting
 */
jQuery.fn.dw_toggle = function(state, fn, noaria) {
    return this.each(function() {
        var $this = jQuery(this);
        if (typeof state === 'undefined') {
            state = $this.is(':hidden');
        }
        $this[state ? "dw_show" : "dw_hide" ](fn, noaria);
    });
};

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
        jQuery(document).on('click','#page__revisions input[type=checkbox]',
            dw_behaviour.revisionBoxHandler
        );

        jQuery('.bounce').effect('bounce', {times:10}, 2000 );
    },

    /**
     * Looks for an element with the ID scroll__here at scrolls to it
     */
    scrollToMarker: function(){
        var $obj = jQuery('#scroll__here');
        if($obj.length) {
            if($obj.offset().top != 0) {
                jQuery('html, body').animate({
                    scrollTop: $obj.offset().top - 100
                }, 500);
            } else {
                // hidden object have no offset but can still be scrolled into view
                $obj[0].scrollIntoView();
            }
        }
    },

    /**
     * Looks for an element with the ID focus__this at sets focus to it
     */
    focusMarker: function(){
        jQuery('#focus__this').trigger('focus');
    },

    /**
     * Remove all search highlighting when clicking on a highlighted term
     */
    removeHighlightOnClick: function(){
        jQuery('span.search_hit').on('click',
            function(e){
                jQuery(e.target).removeClass('search_hit', 1000);
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
            .on('change', function(e){ e.target.form.submit(); })
            .closest('form').find(':button').not('.show').hide();
    },

    /**
     * Display error for Windows Shares on browsers other than IE
     *
     * @author Michael Klier <chi@chimeric.de>
     */
    checkWindowsShares: function() {
        if(!LANG.nosmblinks || navigator.userAgent.match(/(Trident|MSIE|Edge)/)) {
            // No warning requested or none necessary
            return;
        }

        jQuery('a.windows').on('click', function(){
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
            .on('click',
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
            .trigger('click');
    },

    /**
     * disable multiple revisions checkboxes if two are checked
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     * @author Anika Henke <anika@selfthinker.org>
     */
    revisionBoxHandler: function() {
        var $revisions = jQuery('#page__revisions');
        var $all       = jQuery('input[type=checkbox]', $revisions);
        var $checked   = $all.filter(':checked');
        var $button    = jQuery('button', $revisions);

        if($checked.length < 2) {
            $all.prop('disabled', false);
            $button.prop('disabled', true);
        } else {
            $all.prop('disabled', true);
            $button.prop('disabled', false);
            $checked.each(function(i) {
                jQuery(this).prop('disabled', false);
                if(i>1) {
                    jQuery(this).prop('checked', false);
                }
            });
        }
    }
};

jQuery(dw_behaviour.init);
