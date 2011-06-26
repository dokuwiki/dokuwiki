/*jslint sloppy: true */
/*global dw_index, DEPRECATED */

var index = {
    throbber_delay: dw_index.throbber_delay,

    toggle: function () {
        DEPRECATED();
        dw_index.toggle.apply(dw_index, arguments);
    },

    treeattach: function () {
        DEPRECATED();
        dw_index.treeattach.apply(dw_index, arguments);
    }
};
