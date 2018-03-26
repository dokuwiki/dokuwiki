<?php

namespace dokuwiki\Ui;

use \dokuwiki\Form\Form;

class Search extends Ui
{
    protected $query;
    protected $parsedQuery;
    protected $searchState;
    protected $pageLookupResults = array();
    protected $fullTextResults = array();
    protected $highlight = array();

    /**
     * Search constructor.
     */
    public function __construct()
    {
        global $QUERY;
        $Indexer = idx_get_indexer();

        $this->query = $QUERY;
        $this->parsedQuery = ft_queryParser($Indexer, $QUERY);
        $this->searchState = new SearchState($this->parsedQuery);
    }

    /**
     * run the search
     */
    public function execute()
    {
        $this->pageLookupResults = ft_pageLookup($this->query, true, useHeading('navigation'));
        $this->fullTextResults = ft_pageSearch($this->query, $highlight);
        $this->highlight = $highlight;
    }

    /**
     * display the search result
     *
     * @return void
     */
    public function show()
    {
        $searchHTML = '';

        $searchHTML .= $this->getSearchFormHTML($this->query);

        $searchHTML .= $this->getSearchIntroHTML($this->query);

        $searchHTML .= $this->getPageLookupHTML($this->pageLookupResults);

        $searchHTML .= $this->getFulltextResultsHTML($this->fullTextResults, $this->highlight);

        echo $searchHTML;
    }

    /**
     * Get a form which can be used to adjust/refine the search
     *
     * @param string $query
     *
     * @return string
     */
    protected function getSearchFormHTML($query)
    {
        global $lang, $ID, $INPUT;

        $searchForm = (new Form())->attrs(['method' => 'get'])->addClass('search-results-form');
        $searchForm->setHiddenField('do', 'search');
        $searchForm->setHiddenField('id', $ID);
        $searchForm->setHiddenField('searchPageForm', '1');
        if ($INPUT->has('after')) {
            $searchForm->setHiddenField('after', $INPUT->str('after'));
        }
        if ($INPUT->has('before')) {
            $searchForm->setHiddenField('before', $INPUT->str('before'));
        }
        $searchForm->addFieldsetOpen()->addClass('search-results-form__fieldset');
        $searchForm->addTextInput('q')->val($query)->useInput(false);
        $searchForm->addButton('', $lang['btn_search'])->attr('type', 'submit');

        if ($this->isSearchAssistanceAvailable($this->parsedQuery)) {
            $this->addSearchAssistanceElements($searchForm);
        } else {
            $searchForm->addClass('search-results-form--no-assistance');
            $searchForm->addTagOpen('span')->addClass('search-results-form__no-assistance-message');
            $searchForm->addHTML('FIXME Your query is too complex. Search assistance is unavailable. See <a href="https://doku.wiki/search">doku.wiki/search</a> for more help.');
            $searchForm->addTagClose('span');
        }

        $searchForm->addFieldsetClose();

        trigger_event('SEARCH_FORM_DISPLAY', $searchForm);

        return $searchForm->toHTML();
    }

    /**
     * Decide if the given query is simple enough to provide search assistance
     *
     * @param array $parsedQuery
     *
     * @return bool
     */
    protected function isSearchAssistanceAvailable(array $parsedQuery)
    {
        if (count($parsedQuery['words']) > 1) {
            return false;
        }
        if (!empty($parsedQuery['not'])) {
            return false;
        }

        if (!empty($parsedQuery['phrases'])) {
            return false;
        }

        if (!empty($parsedQuery['notns'])) {
            return false;
        }
        if (count($parsedQuery['ns']) > 1) {
            return false;
        }

        return true;
    }

    /**
     * Add the elements to be used for search assistance
     *
     * @param Form  $searchForm
     */
    protected function addSearchAssistanceElements(Form $searchForm)
    {
        $searchForm->addButton('toggleAssistant', 'toggle search assistant')
            ->attr('type', 'button')
            ->id('search-results-form__show-assistance-button')
            ->addClass('search-results-form__show-assistance-button');

        $searchForm->addTagOpen('div')
            ->addClass('js-advancedSearchOptions')
            ->attr('style', 'display: none;');

        $this->addFragmentBehaviorLinks($searchForm);
        $this->addNamespaceSelector($searchForm);
        $this->addDateSelector($searchForm);

        $searchForm->addTagClose('div');
    }

    protected function addFragmentBehaviorLinks(Form $searchForm)
    {
        $searchForm->addTagOpen('div')->addClass('search-results-form__subwrapper');
        $searchForm->addHTML('fragment behavior: ');

        $this->searchState->addSearchLinkFragment(
            $searchForm,
            'exact match',
            array_map(function($term){return trim($term, '*');},$this->parsedQuery['and'])
        );

        $searchForm->addHTML(', ');

        $this->searchState->addSearchLinkFragment(
            $searchForm,
            'starts with',
            array_map(function($term){return trim($term, '*') . '*';},$this->parsedQuery['and'])
        );

        $searchForm->addHTML(', ');

        $this->searchState->addSearchLinkFragment(
            $searchForm,
            'ends with',
            array_map(function($term){return '*' . trim($term, '*');},$this->parsedQuery['and'])
        );

        $searchForm->addHTML(', ');

        $this->searchState->addSearchLinkFragment(
            $searchForm,
            'contains',
            array_map(function($term){return '*' . trim($term, '*') . '*';},$this->parsedQuery['and'])
        );

        $searchForm->addTagClose('div');
    }

    /**
     * Add the elements for the namespace selector
     *
     * @param Form  $searchForm
     */
    protected function addNamespaceSelector(Form $searchForm)
    {
        $baseNS = empty($this->parsedQuery['ns']) ? '' : $this->parsedQuery['ns'][0];
        $searchForm->addTagOpen('div')->addClass('search-results-form__subwrapper');

        $extraNS = $this->getAdditionalNamespacesFromResults($baseNS);
        if (!empty($extraNS) || $baseNS) {
            $searchForm->addTagOpen('div');
            $searchForm->addHTML('limit to namespace: ');

            if ($baseNS) {
                $this->searchState->addSeachLinkNS(
                    $searchForm,
                    '(remove limit)',
                    ''
                );
            }

            foreach ($extraNS as $ns => $count) {
                $searchForm->addHTML(' ');
                $label = $ns . ($count ? " ($count)" : '');

                $this->searchState->addSeachLinkNS($searchForm, $label, $ns);
            }
            $searchForm->addTagClose('div');
        }

        $searchForm->addTagClose('div');
    }

    /**
     * Parse the full text results for their top namespaces below the given base namespace
     *
     * @param string $baseNS the namespace within which was searched, empty string for root namespace
     *
     * @return array an associative array with namespace => #number of found pages, sorted descending
     */
    protected function getAdditionalNamespacesFromResults($baseNS)
    {
        $namespaces = [];
        $baseNSLength = strlen($baseNS);
        foreach ($this->fullTextResults as $page => $numberOfHits) {
            $namespace = getNS($page);
            if (!$namespace) {
                continue;
            }
            if ($namespace === $baseNS) {
                continue;
            }
            $firstColon = strpos((string)$namespace, ':', $baseNSLength + 1) ?: strlen($namespace);
            $subtopNS = substr($namespace, 0, $firstColon);
            if (empty($namespaces[$subtopNS])) {
                $namespaces[$subtopNS] = 0;
            }
            $namespaces[$subtopNS] += 1;
        }
        arsort($namespaces);
        return $namespaces;
    }

    /**
     * @ToDo: custom date input
     *
     * @param Form $searchForm
     */
    protected function addDateSelector(Form $searchForm) {
        $searchForm->addTagOpen('div')->addClass('search-results-form__subwrapper');
        $searchForm->addHTML('limit by date: ');

        global $INPUT;
        if ($INPUT->has('before') || $INPUT->has('after')) {
            $this->searchState->addSearchLinkTime(
                $searchForm,
                '(remove limit)',
                false,
                false
            );

            $searchForm->addHTML(', ');
        }

        if ($INPUT->str('after') === '1 week ago') {
            $searchForm->addHTML('<span class="active">past 7 days</span>');
        } else {
            $this->searchState->addSearchLinkTime(
                $searchForm,
                'past 7 days',
                '1 week ago',
                false
            );
        }

        $searchForm->addHTML(', ');

        if ($INPUT->str('after') === '1 month ago') {
            $searchForm->addHTML('<span class="active">past month</span>');
        } else {
            $this->searchState->addSearchLinkTime(
                $searchForm,
                'past month',
                '1 month ago',
                false
            );
        }

        $searchForm->addHTML(', ');

        if ($INPUT->str('after') === '1 year ago') {
            $searchForm->addHTML('<span class="active">past year</span>');
        } else {
            $this->searchState->addSearchLinkTime(
                $searchForm,
                'past year',
                '1 year ago',
                false
            );
        }

        $searchForm->addTagClose('div');
    }


    /**
     * Build the intro text for the search page
     *
     * @param string $query the search query
     *
     * @return string
     */
    protected function getSearchIntroHTML($query)
    {
        global $ID, $lang;

        $intro = p_locale_xhtml('searchpage');
        // allow use of placeholder in search intro
        $pagecreateinfo = (auth_quickaclcheck($ID) >= AUTH_CREATE) ? $lang['searchcreatepage'] : '';
        $intro = str_replace(
            array('@QUERY@', '@SEARCH@', '@CREATEPAGEINFO@'),
            array(hsc(rawurlencode($query)), hsc($query), $pagecreateinfo),
            $intro
        );
        return $intro;
    }

    /**
     * Build HTML for a list of pages with matching pagenames
     *
     * @param array $data search results
     *
     * @return string
     */
    protected function getPageLookupHTML($data)
    {
        if (empty($data)) {
            return '';
        }

        global $lang;

        $html = '<div class="search_quickresult">';
        $html .= '<h3>' . $lang['quickhits'] . ':</h3>';
        $html .= '<ul class="search_quickhits">';
        foreach ($data as $id => $title) {
            $link = html_wikilink(':' . $id);
            $eventData = [
                'listItemContent' => [$link],
                'page' => $id,
            ];
            trigger_event('SEARCH_RESULT_PAGELOOKUP', $eventData);
            $html .= '<li>' . implode('', $eventData['listItemContent']) . '</li>';
        }
        $html .= '</ul> ';
        //clear float (see http://www.complexspiral.com/publications/containing-floats/)
        $html .= '<div class="clearer"></div>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Build HTML for fulltext search results or "no results" message
     *
     * @param array $data      the results of the fulltext search
     * @param array $highlight the terms to be highlighted in the results
     *
     * @return string
     */
    protected function getFulltextResultsHTML($data, $highlight)
    {
        global $lang;

        if (empty($data)) {
            return '<div class="nothing">' . $lang['nothingfound'] . '</div>';
        }

        $html = '';
        $html .= '<dl class="search_results">';
        $num = 1;

        foreach ($data as $id => $cnt) {
            $resultLink = html_wikilink(':' . $id, null, $highlight);

            $resultHeader = [$resultLink];


            $restrictQueryToNSLink = $this->restrictQueryToNSLink(getNS($id));
            if ($restrictQueryToNSLink) {
                $resultHeader[] = $restrictQueryToNSLink;
            }

            $snippet = '';
            $lastMod = '';
            $mtime = filemtime(wikiFN($id));
            if ($cnt !== 0) {
                $resultHeader[] = $cnt . ' ' . $lang['hits'];
                if ($num < FT_SNIPPET_NUMBER) { // create snippets for the first number of matches only
                    $snippet = '<dd>' . ft_snippet($id, $highlight) . '</dd>';
                    $lastMod = '<span class="search_results__lastmod">'. $lang['lastmod'] . ' ';
                    $lastMod .= '<time datetime="' . date_iso8601($mtime) . '">'. dformat($mtime) . '</time>';
                    $lastMod .= '</span>';
                }
                $num++;
            }

            $metaLine = '<div class="search_results__metaLine">';
            $metaLine .= $lastMod;
            $metaLine .= '</div>';


            $eventData = [
                'resultHeader' => $resultHeader,
                'resultBody' => [$metaLine, $snippet],
                'page' => $id,
            ];
            trigger_event('SEARCH_RESULT_FULLPAGE', $eventData);
            $html .= '<div class="search_fullpage_result">';
            $html .= '<dt>' . implode(' ', $eventData['resultHeader']) . '</dt>';
            $html .= implode('', $eventData['resultBody']);
            $html .= '</div>';
        }
        $html .= '</dl>';

        return $html;
    }

    /**
     * create a link to restrict the current query to a namespace
     *
     * @param bool|string $ns the namespace to which to restrict the query
     *
     * @return bool|string
     */
    protected function restrictQueryToNSLink($ns)
    {
        if (!$ns) {
            return false;
        }
        if (!$this->isSearchAssistanceAvailable($this->parsedQuery)) {
            return false;
        }
        if (!empty($this->parsedQuery['ns']) && $this->parsedQuery['ns'][0] === $ns) {
            return false;
        }
        $name = '@' . $ns;
        $tmpForm = new Form();
        $this->searchState->addSeachLinkNS($tmpForm, $name, $ns);
        return $tmpForm->toHTML();
    }
}
