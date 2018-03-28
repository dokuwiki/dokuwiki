jQuery(function () {
    'use strict';

    const $searchForm = jQuery('.search-results-form');
    if (!$searchForm.length) {
        return;
    }

    const $toggleAssistanceButton = jQuery('<button>')
        .addClass('toggleAssistant')
        .attr('type', 'button')
        .attr('aria-expanded', 'false')
        .text(LANG.search_toggle_tools)
        .prependTo($searchForm.find('fieldset'))
    ;

    $toggleAssistanceButton.on('click', function () {
        jQuery('.advancedOptions').toggle(0, function () {
            var $me = jQuery(this);
            if ($me.attr('aria-hidden')) {
                $me.removeAttr('aria-hidden');
                $toggleAssistanceButton.attr('aria-expanded', 'true');
                DokuCookie.setValue('sa', 'on');
            } else {
                $me.attr('aria-hidden', 'true');
                $toggleAssistanceButton.attr('aria-expanded', 'false');
                DokuCookie.setValue('sa', 'off');
            }
        });
    });

    if (DokuCookie.getValue('sa') === 'on') {
        $toggleAssistanceButton.click();
    }

    $searchForm.find('.advancedOptions .toggle div.current').on('click', function () {
        var $me = jQuery(this);
        $me.parent().siblings().removeClass('open');
        $me.parent().siblings().find('ul:first').attr('aria-expanded', 'false');
        $me.parent().toggleClass('open');
        if ($me.parent().hasClass('open')) {
            $me.parent().find('ul:first').attr('aria-expanded', 'true');
        } else {
            $me.parent().find('ul:first').attr('aria-expanded', 'false');
        }
    });

});
