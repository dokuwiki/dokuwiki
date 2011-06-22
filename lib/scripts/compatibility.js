/*jslint sloppy: true */
/*global dw_index, dw_qsearch, DEPRECATED_WRAP */

var index = {
    throbber_delay: dw_index.throbber_delay,
    toggle: DEPRECATED_WRAP(dw_index.toggle, dw_index),
    treeattach: DEPRECATED_WRAP(dw_index.treeattach, dw_index)
};

var ajax_quicksearch = {
    init: DEPRECATED_WRAP(dw_qsearch.init, dw_qsearch),
    clear_results: DEPRECATED_WRAP(dw_qsearch.clear_results, dw_qsearch),
    onCompletion: DEPRECATED_WRAP(dw_qsearch.onCompletion, dw_qsearch)
};
