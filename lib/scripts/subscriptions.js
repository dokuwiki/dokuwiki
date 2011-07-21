/*jslint sloppy: true */
/*global jQuery */
/**
 * Hide list subscription style if target is a page
 *
 * @author Adrian Lang <lang@cosmocode.de>
 * @author Pierre Spring <pierre.spring@caillou.ch>
 */
jQuery(function () {
    var $form, $list, $digest;

    $form = jQuery('#subscribe__form');

    if (0 === $form.length) {
        return;
    }

    $list = $form.find("input[name='sub_style'][value='list']");
    $digest = $form.find("input[name='sub_style'][value='digest']");

    $form.find("input[name='sub_target']")
        .click(
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
        .click();
});
