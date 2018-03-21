<?php

namespace dokuwiki\Ui;

use \dokuwiki\Form\Form;

class Search extends Ui
{
    protected $query;
    protected $pageLookupResults = array();
    protected $fullTextResults = array();
    protected $highlight = array();

    /**
     * Search constructor.
     *
     * @param string $query the search query
     */
    public function __construct($query)
    {
        $this->query = $query;
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
        global $lang;

        $Indexer = idx_get_indexer();
        $parsedQuery = ft_queryParser($Indexer, $query);

        $searchForm = (new Form())->attrs(['method' => 'get'])->addClass('search-results-form');
        $searchForm->setHiddenField('do', 'search');
        $searchForm->addFieldsetOpen()->addClass('search-results-form__fieldset');
        $searchForm->addTextInput('id')->val($query);
        $searchForm->addButton('', $lang['btn_search'])->attr('type', 'submit');

        if ($this->isSearchAssistanceAvailable($parsedQuery)) {
            $this->addSearchAssistanceElements($searchForm, $parsedQuery);
        } else {
            $searchForm->addClass('search-results-form--no-assistance');
            $searchForm->addTagOpen('span')->addClass('search-results-form__no-assistance-message');
            $searchForm->addHTML('FIXME Your query is too complex. Search assistance is unavailable. See <a href="https://doku.wiki/search">doku.wiki/search</a> for more help.');
            $searchForm->addTagClose('span');
        }

        $searchForm->addFieldsetClose();

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
     * @param array $parsedQuery
     */
    protected function addSearchAssistanceElements(Form $searchForm, array $parsedQuery)
    {
        $matchType = '';
        $searchTerm = null;
        if (count($parsedQuery['words']) === 1) {
            $searchTerm = $parsedQuery['words'][0];
            $firstChar = $searchTerm[0];
            $lastChar = substr($searchTerm, -1);
            $matchType = 'exact';

            if ($firstChar === '*') {
                $matchType = 'starts';
            }
            if ($lastChar === '*') {
                $matchType = 'ends';
            }
            if ($firstChar === '*' && $lastChar === '*') {
                $matchType = 'contains';
            }
            $searchTerm = trim($searchTerm, '*');
        }

        $searchForm->addTextInput(
            'searchTerm',
            '',
            $searchForm->findPositionByAttribute('type', 'submit')
        )
            ->val($searchTerm)
            ->attr('style', 'display: none;');
        $searchForm->addButton('toggleAssistant', 'toggle search assistant')
            ->attr('type', 'button')
            ->id('search-results-form__show-assistance-button')
            ->addClass('search-results-form__show-assistance-button');

        $searchForm->addTagOpen('div')
            ->addClass('js-advancedSearchOptions')
            ->attr('style', 'display: none;');

        $searchForm->addTagOpen('div')->addClass('search-results-form__subwrapper');
        $searchForm->addRadioButton('matchType', 'exact Match FM')->val('exact')->attr('checked',
            $matchType === 'exact' ?: null);
        $searchForm->addRadioButton('matchType', 'starts with FM')->val('starts')->attr('checked',
            $matchType === 'starts' ?: null);
        $searchForm->addRadioButton('matchType', 'ends with FM')->val('ends')->attr('checked',
            $matchType === 'ends' ?: null);
        $searchForm->addRadioButton('matchType', 'contains FM')->val('contains')->attr('checked',
            $matchType === 'contains' ?: null);
        $searchForm->addTagClose('div');

        $this->addNamespaceSelector($searchForm, $parsedQuery);

        $searchForm->addTagClose('div');
    }

    /**
     * Add the elements for the namespace selector
     *
     * @param Form  $searchForm
     * @param array $parsedQuery
     */
    protected function addNamespaceSelector(Form $searchForm, array $parsedQuery)
    {
        $baseNS = empty($parsedQuery['ns']) ? '' : $parsedQuery['ns'][0];
        $namespaces = [];
        $searchForm->addTagOpen('div')->addClass('search-results-form__subwrapper');
        if ($baseNS) {
            $searchForm->addRadioButton('namespace', '(no namespace FIXME)')->val('');
            $parts = [$baseNS => count($this->fullTextResults)];
            $upperNameSpace = $baseNS;
            while ($upperNameSpace = getNS($upperNameSpace)) {
                $parts[$upperNameSpace] = 0;
            }
            $namespaces = array_reverse($parts);
        };

        $namespaces = array_merge($namespaces, $this->getAdditionalNamespacesFromResults($baseNS));

        foreach ($namespaces as $extraNS => $count) {
            $label = $extraNS . ($count ? " ($count)" : '');
            $namespaceCB = $searchForm->addRadioButton('namespace', $label)->val($extraNS);
            if ($extraNS === $baseNS) {
                $namespaceCB->attr('checked', true);
            }
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
            $hits = '';
            $snippet = '';
            if ($cnt !== 0) {
                $hits = $cnt . ' ' . $lang['hits'];
                if ($num < FT_SNIPPET_NUMBER) { // create snippets for the first number of matches only
                    $snippet = '<dd>' . ft_snippet($id, $highlight) . '</dd>';
                }
                $num++;
            }

            $eventData = [
                'resultHeader' => [$resultLink, $hits],
                'resultBody' => [$snippet],
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
}
