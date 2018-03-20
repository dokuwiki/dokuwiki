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
    const $queryInput = $searchForm.find('[name="id"]');
    const $termInput = $searchForm.find('[name="searchTerm"]');

    $toggleAssistanceButton.on('click', function () {
        jQuery('.js-advancedSearchOptions').toggle();
        $queryInput.toggle();
        $termInput.toggle();
    });


    const $matchTypeSwitcher = $searchForm.find('[name="matchType"]');
    const $namespaceSwitcher = $searchForm.find('[name="namespace"]');
    const $refiningElements = $termInput.add($matchTypeSwitcher).add($namespaceSwitcher);
    $refiningElements.on('input change', function () {
        $queryInput.val(
            rebuildQuery(
                $termInput.val(),
                $matchTypeSwitcher.filter(':checked').val(),
                $namespaceSwitcher.filter(':checked').val()
            )
        );
    });

    /**
     * Rebuild the search query from the parts
     *
     * @param {string} searchTerm the word which is to be searched
     * @param {enum} matchType the type of matching that is to be done
     * @param {string} namespace list of namespaces to which to limit the search
     *
     * @return {string} the query string for the actual search
     */
    function rebuildQuery(searchTerm, matchType, namespace) {
        let query = '';

        switch (matchType) {
        case 'contains':
            query = '*' + searchTerm + '*';
            break;
        case 'starts':
            query = '*' + searchTerm;
            break;
        case 'ends':
            query = searchTerm + '*';
            break;
        default:
            query = searchTerm;
        }

        if (namespace && namespace.length) {
            query += ' @' + namespace;
        }

        return query;
    }
});
