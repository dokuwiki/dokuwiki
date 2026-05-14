/**
 * Javascript for index view
 *
 * @author Gerrit Uitslag <klapinklapin@gmail.com>
 */

jQuery(function () {

    jQuery('.indexmenu_nojs').each(function () {
        let $tree = jQuery(this);
        let jsajax = $tree.data('jsajax');

        $tree.dw_tree({
            toggle_selector: 'a.indexmenu_idx',
            load_data: function (show_sublist, $clicky) {

                jQuery.post(
                    DOKU_BASE + 'lib/exe/ajax.php',
                    'call=indexmenu&req=index&nojs=1&' + $clicky[0].search.substring(1) + '&max=1' + decodeURIComponent(jsajax),
                    show_sublist,
                    'html'
                );
            }
        });
    });

});
