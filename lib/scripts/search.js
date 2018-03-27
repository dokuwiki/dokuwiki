jQuery(function () {
    'use strict';

    const $searchForm = jQuery('.search-results-form');
    if (!$searchForm.length) {
        return;
    }

    const $toggleAssistanceButton = $searchForm.find('button.toggleAssistant');
    if (!$toggleAssistanceButton.length){
        return;
    }

    $toggleAssistanceButton.on('click', function () {
        jQuery('.advancedOptions').toggle();
        DokuCookie.setValue('sa', !DokuCookie.getValue('sa'));
    });

    if (DokuCookie.getValue('sa')) {
        $toggleAssistanceButton.click();
    }

    $searchForm.find('.advancedOptions .toggle div.current').on('click', function() {
        var $me = jQuery(this);
        $me.parent().siblings().removeClass('open');
        $me.parent().toggleClass('open');
    });

});
