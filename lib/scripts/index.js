/*jslint white: true, onevar: true, undef: true, nomen: true, eqeqeq: true, plusplus: true, bitwise: true, regexp: false, newcap: true, immed: true */
/*global jQuery, window, DOKU_BASE, DEPRECATED, bind*/

/**
 * Javascript for index view
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Pierre Spring <pierre.spring@caillou.ch>
 */

var dw_index = {

    /**
     * Delay in ms before showing the throbber.
     * Used to skip the throbber for fast AJAX calls.
     */
    throbber_delay: 500,

    /**
     * Initialize tree when the DOM is ready.
     */
    init: function () {
        dw_index.treeattach('#index__tree');
    },

    treeattach: function (obj) {
        jQuery(obj).delegate('a.idx_dir', 'click', dw_index.toggle);
    },

    /**
     * Open or close a subtree using AJAX
     * The contents of subtrees are "cached" until the page is reloaded.
     * A "loading" indicator is shown only when the AJAX call is slow.
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     * @author Ben Coburn <btcoburn@silicodon.net>
     * @author Pierre Spring <pierre.spring@caillou.ch>
     */
    toggle: function (e, _this) {
        e.preventDefault();

        var $listitem, $sublist, timeout, $clicky, show_sublist;

        if (_this) {
            DEPRECATED('Use dw_index.toggle(e) (or dw_index.toggle.call(clicky, e) if you need to override clicky), not dw_index.toggle(e, clicky)');
        }

        $clicky = jQuery(_this || this);
        $listitem = $clicky.closest('li');
        $sublist = $listitem.find('ul').first();

        // if already open, close by removing the sublist
        if ($listitem.hasClass('open')) {
            $sublist.slideUp('fast',
                function () {
                    $listitem.addClass('closed').removeClass('open');
                }
            );
            return;
        }

        show_sublist = function (data) {
            if (!$listitem.hasClass('open') || $sublist.parent().length === 0) {
                $listitem.append($sublist).addClass('open').removeClass('closed');
            }
            $sublist.hide();
            if (data) {
                $sublist.html(data);
            }
            $sublist.slideDown('fast');
        };

        // just show if already loaded
        if ($sublist.length > 0) {
            show_sublist();
            return;
        }

        //prepare the new ul
        $sublist = jQuery('<ul class="idx"/>');

        timeout = window.setTimeout(
            bind(show_sublist, '<li><img src="' + DOKU_BASE + 'lib/images/throbber.gif" alt="loading..." title="loading..." /></li>'), dw_index.throbber_delay);

        jQuery.post(
            DOKU_BASE + 'lib/exe/ajax.php',
            $clicky[0].search.substr(1) + '&call=index',
            function (data) {
                window.clearTimeout(timeout);
                show_sublist(data);
            },
            'html'
        );
    }
};

jQuery(dw_index.init);
