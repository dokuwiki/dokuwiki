/*jslint white: true, onevar: true, undef: true, nomen: true, eqeqeq: true, plusplus: true, bitwise: true, regexp: false, newcap: true, immed: true, sloppy: true */
/*global jQuery, window, DOKU_BASE, DEPRECATED, bind*/

jQuery.fn.dw_tree = function(overrides) {
    var dw_tree = {

        /**
         * Delay in ms before showing the throbber.
         * Used to skip the throbber for fast AJAX calls.
         */
        throbber_delay: 500,

        $obj: this,

        toggle_selector: 'a.idx_dir',

        init: function () {
            this.$obj.delegate(this.toggle_selector, 'click', this,
                               this.toggle);
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
        toggle: function (e) {
            var $listitem, $sublist, timeout, $clicky, show_sublist, dw_tree;

            e.preventDefault();

            $clicky = jQuery(this);
            $listitem = $clicky.closest('li');
            $sublist = $listitem.find('ul').first();
            dw_tree = e.data;

            // if already open, close by hiding the sublist
            if ($listitem.hasClass('open')) {
                $sublist.dw_hide(function () {
                    dw_tree.close($clicky);
                    $listitem.addClass('closed').removeClass('open');
                });
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
                $sublist.dw_show();
            };

            // just show if already loaded
            if ($sublist.length > 0) {
                show_sublist();
                return;
            }

            //prepare the new ul
            $sublist = jQuery('<ul class="idx"/>');

            timeout = window.setTimeout(
                bind(show_sublist, '<li><img src="' + DOKU_BASE + 'lib/images/throbber.gif" alt="loading..." title="loading..." /></li>'), dw_tree.throbber_delay);

            dw_tree.load_data(function (data) {
                                  window.clearTimeout(timeout);
                                  show_sublist(data);
                              }, $clicky);
        },

        close: function ($clicky) {
        },

        load_data: function (show_data, $clicky) {
            show_data();
        }
    };

    jQuery.extend(dw_tree, overrides);

    if (!overrides.deferInit) {
        dw_tree.init();
    }

    return dw_tree;
};
