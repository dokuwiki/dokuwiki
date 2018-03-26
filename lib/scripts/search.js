jQuery(function () {
    'use strict';

    const $searchForm = jQuery('.search-results-form');
    if (!$searchForm.length) {
        return;
    }
    if (!$searchForm.find('#search-results-form__show-assistance-button').length){
        return;
    }
    const $toggleAssistanceButton = $searchForm.find('#search-results-form__show-assistance-button');

    $toggleAssistanceButton.on('click', function () {
        jQuery('.js-advancedSearchOptions').toggle();
        DokuCookie.setValue('showSearchAssistant', !DokuCookie.getValue('showSearchAssistant'));
    });

    if (DokuCookie.getValue('showSearchAssistant')) {
        $toggleAssistanceButton.click();
    }

    $searchForm.find('.js-search-tool .js-current').on('click', function() {
        $searchForm.find('.js-current').show();
        $searchForm.find('.js-optionsList').hide();
        jQuery(this).hide();
        jQuery(this).next('.js-optionsList').show();
    });

});
