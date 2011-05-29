/*jslint white: true, onevar: true, undef: true, nomen: true, eqeqeq: true, plusplus: true, bitwise: true, regexp: false, strict: true, newcap: true, immed: true */
/*global jQuery, window, DOKU_BASE*/
"use strict";

/**
 * Javascript for index view
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Pierre Spring <pierre.spring@caillou.ch>
 */

(function ($) {
    var throbber_delay, toggle;

     /**
     * Delay in ms before showing the throbber.
     * Used to skip the throbber for fast AJAX calls.
     */
    throbber_delay = 500;

    /**
     * Open or close a subtree using AJAX
     * The contents of subtrees are "cached" untill the page is reloaded.
     * A "loading" indicator is shown only when the AJAX call is slow.
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     * @author Ben Coburn <btcoburn@silicodon.net>
     * @author Pierre Spring <pierre.spring@caillou.ch>
     */
    toggle = function (e) {

        var listitem, sublist, timeout, ul, clicky;

        clicky = $(this);
        listitem = clicky.parentsUntil('li').last().parent();
        sublist = listitem.find('ul').first();

        // if already open, close by removing the sublist
        if (listitem.hasClass('open')) {
            sublist.slideUp(
                function () {
                    listitem.addClass('closed').removeClass('open');
                }
            );
            e.preventDefault();
            return;
        }

        // just show if already loaded
        if (sublist.size() > 0 && !listitem.hasClass('open')) {
            listitem.addClass('open').removeClass('closed');
            sublist.slideDown();
            e.preventDefault();
            return;
        }

        //prepare the new ul
        ul = $('<ul class="idx"/>');

        timeout = window.setTimeout(function () {
            // show the throbber as needed
            if (!listitem.hasClass('open')) {
                ul.html('<li><img src="' + DOKU_BASE + 'lib/images/throbber.gif" alt="loading..." title="loading..." /></li>');
                listitem
                    .append(ul)
                    .addClass('open')
                    .removeClass('closed');
            }
        }, throbber_delay);

        $.post(
            DOKU_BASE + 'lib/exe/ajax.php',
            clicky.attr('search').substr(1) + '&call=index',
            function (data) {
                window.clearTimeout(timeout);
                ul.html(data);
                if (listitem.className !== 'open') {
                    if (ul.parent().size() === 0) {
                        // if the UL has not been attached when showing the
                        // throbber, then let's do it now.
                        listitem.append(ul);
                    }
                    listitem.addClass('open').removeClass('closed');
                }
            },
            'html'
        );
        e.preventDefault();
    };

    $(function () {
        // Initialze tree when the DOM is ready.
        $('#index__tree').delegate('a.idx_dir', 'click', toggle);
    });
}(jQuery));