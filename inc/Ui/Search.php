<?php

namespace dokuwiki\Ui;

use \dokuwiki\Form\Form;

class Search extends Ui
{
    protected $query;
    protected $parsedQuery;
    protected $pageLookupResults = array();
    protected $fullTextResults = array();
    protected $highlight = array();

    /**
     * Search constructor.
     *
     * @param string $query the search query
     */
    public function __construct()
    {
        global $QUERY;
        $Indexer = idx_get_indexer();

        $this->query = $QUERY;
        $this->parsedQuery = ft_queryParser($Indexer, $QUERY);
    }

    /**
     * run the search
     */
    public function execute()
    {
        $this->pageLookupResults = $this->filterResultsByTime(
            ft_pageLookup($this->query, true, useHeading('navigation'))
        );
        $this->fullTextResults = $this->filterResultsByTime(
            ft_pageSearch($this->query, $highlight)
        );
        $this->highlight = $highlight;
    }

    /**
     * @param array $results search results in the form pageid => value
     *
     * @return array
     */
    protected function filterResultsByTime(array $results) {
        global $INPUT;
        if ($INPUT->has('after') || $INPUT->has('before')) {
            $after = $INPUT->str('after');
            $after = is_int($after) ? $after : strtotime($after);

            $before = $INPUT->str('before');
            $before = is_int($before) ? $before : strtotime($before);

            // todo: should we filter $this->pageLookupResults as well?
            foreach ($results as $id => $value) {
                $mTime = filemtime(wikiFN($id));
                if ($after && $after > $mTime) {
                    unset($results[$id]);
                    continue;
                }
                if ($before && $before < $mTime) {
                    unset($results[$id]);
                }
            }
        }

        return $results;
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
            $this->addSearchAssistanceElements($searchForm, $this->parsedQuery);
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
     * @param array $parsedQuery
     */
    protected function addSearchAssistanceElements(Form $searchForm, array $parsedQuery)
    {
        $searchForm->addButton('toggleAssistant', 'toggle search assistant')
            ->attr('type', 'button')
            ->id('search-results-form__show-assistance-button')
            ->addClass('search-results-form__show-assistance-button');

        $searchForm->addTagOpen('div')
            ->addClass('js-advancedSearchOptions')
            ->attr('style', 'display: none;');

        $this->addFragmentBehaviorLinks($searchForm, $parsedQuery);
        $this->addNamespaceSelector($searchForm, $parsedQuery);
        $this->addDateSelector($searchForm, $parsedQuery);

        $searchForm->addTagClose('div');
    }

    protected function addFragmentBehaviorLinks(Form $searchForm, array $parsedQuery)
    {
        $searchForm->addTagOpen('div')->addClass('search-results-form__subwrapper');
        $searchForm->addHTML('fragment behavior: ');

        $this->addSearchLink(
            $searchForm,
            'exact match',
            array_map(function($term){return trim($term, '*');},$this->parsedQuery['and'])
        );

        $searchForm->addHTML(', ');

        $this->addSearchLink(
            $searchForm,
            'starts with',
            array_map(function($term){return trim($term, '*') . '*';},$this->parsedQuery['and'])
        );

        $searchForm->addHTML(', ');

        $this->addSearchLink(
            $searchForm,
            'ends with',
            array_map(function($term){return '*' . trim($term, '*');},$this->parsedQuery['and'])
        );

        $searchForm->addHTML(', ');

        $this->addSearchLink(
            $searchForm,
            'contains',
            array_map(function($term){return '*' . trim($term, '*') . '*';},$this->parsedQuery['and'])
        );

        $searchForm->addTagClose('div');
    }

    protected function addSearchLink(
        Form $searchForm,
        $label,
        array $and = null,
        array $ns = null,
        array $not = null,
        array $notns = null,
        array $phrases = null,
        $after = null,
        $before = null
    ) {
        global $INPUT, $ID;
        if (null === $and) {
            $and = $this->parsedQuery['and'];
        }
        if (null === $ns) {
            $ns = $this->parsedQuery['ns'];
        }
        if (null === $not) {
            $not = $this->parsedQuery['not'];
        }
        if (null === $phrases) {
            $phrases = $this->parsedQuery['phrases'];
        }
        if (null === $notns) {
            $notns = $this->parsedQuery['notns'];
        }
        if (null === $after) {
            $after = $INPUT->str('after');
        }
        if (null === $before) {
            $before = $INPUT->str('before');
        }

        $newQuery = ft_queryUnparser_simple(
            $and,
            $not,
            $phrases,
            $ns,
            $notns
        );
        $hrefAttributes = ['do' => 'search', 'searchPageForm' => '1', 'q' => $newQuery];
        if ($after) {
            $hrefAttributes['after'] = $after;
        }
        if ($before) {
            $hrefAttributes['before'] = $before;
        }
        $searchForm->addTagOpen('a')
            ->attrs([
                'href' => wl($ID, $hrefAttributes, false, '&')
            ])
        ;
        $searchForm->addHTML($label);
        $searchForm->addTagClose('a');
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
        $searchForm->addTagOpen('div')->addClass('search-results-form__subwrapper');

        $extraNS = $this->getAdditionalNamespacesFromResults($baseNS);
        if (!empty($extraNS) || $baseNS) {
            $searchForm->addTagOpen('div');
            $searchForm->addHTML('limit to namespace: ');

            if ($baseNS) {
                $this->addSearchLink(
                    $searchForm,
                    '(remove limit)',
                    null,
                    [],
                    null,
                    []
                );
            }

            foreach ($extraNS as $extraNS => $count) {
                $searchForm->addHTML(' ');
                $label = $extraNS . ($count ? " ($count)" : '');

                $this->addSearchLink($searchForm, $label, null, [$extraNS], null, []);
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
     * @ToDo: we need to remember this date when clicking on other links
     * @ToDo: custom date input
     *
     * @param Form $searchForm
     * @param      $parsedQuery
     */
    protected function addDateSelector(Form $searchForm, $parsedQuery) {
        $searchForm->addTagOpen('div')->addClass('search-results-form__subwrapper');
        $searchForm->addHTML('limit by date: ');

        global $INPUT;
        if ($INPUT->has('before') || $INPUT->has('after')) {
            $this->addSearchLink(
                $searchForm,
                '(remove limit)',
                null,
                null,
                null,
                null,
                null,
                false,
                false
            );

            $searchForm->addHTML(', ');
        }

        if ($INPUT->str('after') === '1 week ago') {
            $searchForm->addHTML('<span class="active">past 7 days</span>');
        } else {
            $this->addSearchLink(
                $searchForm,
                'past 7 days',
                null,
                null,
                null,
                null,
                null,
                '1 week ago',
                false
            );
        }

        $searchForm->addHTML(', ');

        if ($INPUT->str('after') === '1 month ago') {
            $searchForm->addHTML('<span class="active">past month</span>');
        } else {
            $this->addSearchLink(
                $searchForm,
                'past month',
                null,
                null,
                null,
                null,
                null,
                '1 month ago',
                false
            );
        }

        $searchForm->addHTML(', ');

        if ($INPUT->str('after') === '1 year ago') {
            $searchForm->addHTML('<span class="active">past year</span>');
        } else {
            $this->addSearchLink(
                $searchForm,
                'past year',
                null,
                null,
                null,
                null,
                null,
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
        $this->addSearchLink($tmpForm, $name, null, [$ns], null, []);
        return $tmpForm->toHTML();
    }
}
