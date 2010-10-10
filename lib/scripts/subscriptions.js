/**
 * Hide list subscription style if target is a page
 *
 * @author Adrian Lang <lang@cosmocode.de>
 * @author Pierre Spring <pierre.spring@caillou.ch>
 */
(function ($) {
    $(function () {
        var form, list, digest;
        
        form = $('#subscribe__form');

        if (0 === form.size()) {
            return;
        }
        
        list = form.find("input[name='sub_style'][value='list']");
        digest = form.find("input[name='sub_style'][value='digest']");

        form.find("input[name='sub_target']")
            .click(
                function () {
                    var input = $(this);
                    if (!input.is(':checked')) {
                        return;
                    }

                    if (input.val().match(/:$/)) {
                        list.parent().show();
                    } else {
                        list.parent().hide();
                        if (list.is(':checked')) {
                            digest.attr('checked', 'checked');
                        }
                    }
                }
            )
            .filter(':checked')
            .click();
    });
}(jQuery));