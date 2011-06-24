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
                var $input = jQuery(this);
                if (!$input.prop('checked')) {
                    return;
                }

                if ($input.val().match(/:$/)) {
                    $list.parent().slideDown('fast');
                } else {
                    $list.parent().slideUp('fast');
                    if ($list.prop('checked')) {
                        $digest.prop('checked', 'checked');
                    }
                }
            }
        )
        .filter(':checked')
        .click();
});
