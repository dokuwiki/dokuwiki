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
        dw_behaviour.pageRestoreConfirm();
        dw_behaviour.securityCheck();

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
     * Display confirm dialog on page restore action
     */
    pageRestoreConfirm: function(){
        jQuery('#dokuwiki__pagetools li.revert a').on('click',
            function() {
                return confirm(LANG.restore_confirm);
            }
        );
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
     * When a <select> or <input> tag has the class "quickselect", this script will
     * automatically submit its parent form when the select value changes.
     * It also hides the submit button of the form.
     *
     * This includes a workaround a weird behaviour when the submit button has a name
     *
     * @link https://trackjs.com/blog/when-form-submit-is-not-a-function/
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    quickSelect: function(){
        jQuery('.quickselect')
            .change(function(e){ HTMLFormElement.prototype.submit.call(e.target.form); })
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
        var $all       = jQuery('input[type="checkbox"][name="rev2[]"]', $revisions);
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
    },

    /**
     * Check that access to the data directory is properly secured
     *
     * A successful check (a 403 error was returned when loading the image) is saved
     * to session storage and not repeated again until the next browser session. This
     * avoids overeager security bans (see #3363)
     */
    securityCheck: function () {
        var $checkDiv = jQuery('#security__check');
        if (!$checkDiv.length) return;
        if (sessionStorage.getItem('dw-security-check:' + DOKU_BASE)) {
            // check was already executed successfully
            $checkDiv.remove();
            return;
        }

        var img = new Image();
        img.onerror = function () {
            // successful check will not be repeated during session
            $checkDiv.remove();
            sessionStorage.setItem('dw-security-check:' + DOKU_BASE, true);
        };
        img.onload = function () {
            // check failed, display a warning message
            $checkDiv.html(LANG.data_insecure);
            $checkDiv.addClass('error');
        };
        img.src = $checkDiv.data('src') + '?t=' + Date.now();
    }
};

jQuery(dw_behaviour.init);
