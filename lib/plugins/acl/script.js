/**
 * ACL Manager AJAX enhancements
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
var dw_acl = {
    /**
     * Initialize the object and attach the event handlers
     */
    init: function () {
        var $tree;

        //FIXME only one underscore!!
        if (jQuery('#acl_manager').length === 0) {
            return;
        }

        jQuery('#acl__user select').change(dw_acl.userselhandler);
        jQuery('#acl__user input[type=submit]').click(dw_acl.loadinfo);

        $tree = jQuery('#acl__tree');
        $tree.dw_tree({toggle_selector: 'img',
                       load_data: function (show_sublist, $clicky) {
                           // get the enclosed link and the edit form
                           var $frm = jQuery('#acl__detail form');

                           jQuery.post(
                               DOKU_BASE + 'lib/plugins/acl/ajax.php',
                               jQuery.extend(dw_acl.parseatt($clicky.parent().find('a')[0].search),
                                             {ajax: 'tree',
                                              current_ns: $frm.find('input[name=ns]').val(),
                                              current_id: $frm.find('input[name=id]').val()}),
                               show_sublist,
                               'html'
                           );
                       },

                       toggle_display: function ($clicky, opening) {
                           $clicky.attr('src',
                                        DOKU_BASE + 'lib/images/' +
                                        (opening ? 'minus' : 'plus') + '.gif');
                       }});
        $tree.delegate('a', 'click', dw_acl.treehandler);
    },

    /**
     * Handle user dropdown
     *
     * Hides or shows the user/group entry box depending on what was selected in the
     * dropdown element
     */
    userselhandler: function () {
        // make entry field visible/invisible
        jQuery('#acl__user input').toggle(this.value === '__g__' ||
                                          this.value === '__u__');
        dw_acl.loadinfo();
    },

    /**
     * Load the current permission info and edit form
     */
    loadinfo: function () {
        jQuery('#acl__info').load(
            DOKU_BASE + 'lib/plugins/acl/ajax.php',
            jQuery('#acl__detail form').serialize() + '&ajax=info'
        );
        return false;
    },

    /**
     * parse URL attributes into a associative array
     *
     * @todo put into global script lib?
     */
    parseatt: function (str) {
        if (str[0] === '?') {
            str = str.substr(1);
        }
        var attributes = {};
        var all = str.split('&');
        for (var i = 0; i < all.length; i++) {
            var att = all[i].split('=');
            attributes[att[0]] = decodeURIComponent(att[1]);
        }
        return attributes;
    },

    /**
     * Handles clicks to the tree nodes
     */
    treehandler: function () {
        var $link, $frm;

        $link = jQuery(this);

            // remove highlighting
            jQuery('#acl__tree a.cur').removeClass('cur');

            // add new highlighting
        $link.addClass('cur');

            // set new page to detail form
        $frm = jQuery('#acl__detail form');
        if ($link.hasClass('wikilink1')) {
            $frm.find('input[name=ns]').val('');
            $frm.find('input[name=id]').val(dw_acl.parseatt($link[0].search).id);
        } else if ($link.hasClass('idx_dir')) {
            $frm.find('input[name=ns]').val(dw_acl.parseatt($link[0].search).ns);
            $frm.find('input[name=id]').val('');
            }
        dw_acl.loadinfo();

        return false;
    }
};

jQuery(dw_acl.init);

var acl = {
    init: DEPRECATED_WRAP(dw_acl.init, dw_acl),
    userselhandler: DEPRECATED_WRAP(dw_acl.userselhandler, dw_acl),
    loadinfo: DEPRECATED_WRAP(dw_acl.loadinfo, dw_acl),
    parseatt: DEPRECATED_WRAP(dw_acl.parseatt, dw_acl),
    treehandler: DEPRECATED_WRAP(dw_acl.treehandler, dw_acl)
};
