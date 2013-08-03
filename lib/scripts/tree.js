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
            jQuery('ul:first', this.$obj).attr('role', 'tree');
            jQuery('ul', this.$obj).not(':first').attr('role', 'group');
            jQuery('li', this.$obj).attr('role', 'treeitem');
            jQuery('li.open > ul', this.$obj).attr('aria-expanded', 'true');
            jQuery('li.closed > ul', this.$obj).attr('aria-expanded', 'false');
            jQuery('li.closed', this.$obj).attr('aria-live', 'assertive');
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
            var $listitem, $sublist, timeout, $clicky, show_sublist, dw_tree, opening;

            e.preventDefault();

            dw_tree = e.data;
            $clicky = jQuery(this);
            $listitem = $clicky.closest('li');
            $sublist = $listitem.find('ul').first();
            opening = $listitem.hasClass('closed');
            dw_tree.toggle_display($clicky, opening);
            if ($sublist.is(':visible')) {
                $listitem.removeClass('open').addClass('closed');
                $sublist.attr('aria-expanded', 'false');
            } else {
                $listitem.removeClass('closed').addClass('open');
                $sublist.attr('aria-expanded', 'true');
            }

            // if already open, close by hiding the sublist
            if (!opening) {
                $sublist.dw_hide();
                return;
            }

            show_sublist = function (data) {
                $sublist.hide();
                if (typeof data !== 'undefined') {
                    $sublist.html(data);
                    $sublist.parent().attr('aria-busy', 'false').removeAttr('aria-live');
                    jQuery('li.closed', $sublist).attr('aria-live', 'assertive');
                }
                if ($listitem.hasClass('open')) {
                    // Only show if user didn’t close the list since starting
                    // to load the content
                    $sublist.dw_show();
                }
            };

            // just show if already loaded
            if ($sublist.length > 0) {
                show_sublist();
                return;
            }

            //prepare the new ul
            $sublist = jQuery('<ul class="idx" role="group"/>');
            $listitem.append($sublist);

            timeout = window.setTimeout(
                bind(show_sublist, '<li aria-busy="true"><img src="' + DOKU_BASE + 'lib/images/throbber.gif" alt="loading..." title="loading..." /></li>'), dw_tree.throbber_delay);

            dw_tree.load_data(function (data) {
                                  window.clearTimeout(timeout);
                                  show_sublist(data);
                              }, $clicky);
        },

        toggle_display: function ($clicky, opening) {
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
