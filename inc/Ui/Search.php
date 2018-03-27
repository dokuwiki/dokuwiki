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
     *
     * @param array  $pageLookupResults
     * @param array  $fullTextResults
     * @param string $highlight
     */
    public function __construct(array $pageLookupResults, array $fullTextResults, $highlight)
    {
        global $QUERY;
        $Indexer = idx_get_indexer();

        $this->query = $QUERY;
        $this->parsedQuery = ft_queryParser($Indexer, $QUERY);
        $this->searchState = new SearchState($this->parsedQuery);

        $this->pageLookupResults = $pageLookupResults;
        $this->fullTextResults = $fullTextResults;
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
        $searchForm->setHiddenField('sf', '1');
        if ($INPUT->has('dta')) {
            $searchForm->setHiddenField('dta', $INPUT->str('dta'));
        }
        if ($INPUT->has('dtb')) {
            $searchForm->setHiddenField('dtb', $INPUT->str('dtb'));
        }
        if ($INPUT->has('srt')) {
            $searchForm->setHiddenField('srt', $INPUT->str('srt'));
        }
        $searchForm->addFieldsetOpen()->addClass('search-form');
        $searchForm->addTextInput('q')->val($query)->useInput(false);
        $searchForm->addButton('', $lang['btn_search'])->attr('type', 'submit');

        $this->addSearchAssistanceElements($searchForm);

        $searchForm->addFieldsetClose();

        trigger_event('FORM_SEARCH_OUTPUT', $searchForm);

        return $searchForm->toHTML();
    }

    protected function addSortTool(Form $searchForm)
    {
        global $INPUT, $lang;

        $options = [
            'hits' => [
                'label' => $lang['search_sort_by_hits'],
                'sort' => '',
            ],
            'mtime' => [
                'label' => $lang['search_sort_by_mtime'],
                'sort' => 'mtime',
            ],
        ];
        $activeOption = 'hits';

        if ($INPUT->str('srt') === 'mtime') {
            $activeOption = 'mtime';
        }

        $searchForm->addTagOpen('div')->addClass('toggle');
        // render current
        $currentWrapper = $searchForm->addTagOpen('div')->addClass('current');
        if ($activeOption !== 'hits') {
            $currentWrapper->addClass('changed');
        }
        $searchForm->addHTML($options[$activeOption]['label']);
        $searchForm->addTagClose('div');

        // render options list
        $searchForm->addTagOpen('ul');

        foreach ($options as $key => $option) {
            $listItem = $searchForm->addTagOpen('li');

            if ($key === $activeOption) {
                $listItem->addClass('active');
                $searchForm->addHTML($option['label']);
            } else {
                $this->searchState->addSearchLinkSort(
                    $searchForm,
                    $option['label'],
                    $option['sort']
                );
            }
            $searchForm->addTagClose('li');
        }
        $searchForm->addTagClose('ul');

        $searchForm->addTagClose('div');

    }

    protected function isNamespaceAssistanceAvailable(array $parsedQuery) {
        if (preg_match('/[\(\)\|]/', $parsedQuery['query']) === 1) {
            return false;
        }

        return true;
    }

    protected function isFragmentAssistanceAvailable(array $parsedQuery) {
        if (preg_match('/[\(\)\|]/', $parsedQuery['query']) === 1) {
            return false;
        }

        if (!empty($parsedQuery['phrases'])) {
            return false;
        }

        return true;
    }

    /**
     * Add the elements to be used for search assistance
     *
     * @param Form $searchForm
     */
    protected function addSearchAssistanceElements(Form $searchForm)
    {
        // FIXME localize
        $searchForm->addButton('toggleAssistant', 'toggle search assistant')
            ->attr('type', 'button')
            ->addClass('toggleAssistant');

        $searchForm->addTagOpen('div')
            ->addClass('advancedOptions')
            ->attr('style', 'display: none;');

        $this->addFragmentBehaviorLinks($searchForm);
        $this->addNamespaceSelector($searchForm);
        $this->addDateSelector($searchForm);
        $this->addSortTool($searchForm);

        $searchForm->addTagClose('div');
    }

    protected function addFragmentBehaviorLinks(Form $searchForm)
    {
        if (!$this->isFragmentAssistanceAvailable($this->parsedQuery)) {
            return;
        }
        global $lang;

        $options = [
            'exact' => [
                'label' => $lang['search_exact_match'],
                'and' => array_map(function ($term) {
                    return trim($term, '*');
                }, $this->parsedQuery['and']),
                'not' => array_map(function ($term) {
                    return trim($term, '*');
                }, $this->parsedQuery['not']),
            ],
            'starts' => [
                'label' => $lang['search_starts_with'],
                'and' => array_map(function ($term) {
                    return trim($term, '*') . '*';
                }, $this->parsedQuery['and']),
                'not' => array_map(function ($term) {
                    return trim($term, '*') . '*';
                }, $this->parsedQuery['not']),
            ],
            'ends' => [
                'label' => $lang['search_ends_with'],
                'and' => array_map(function ($term) {
                    return '*' . trim($term, '*');
                }, $this->parsedQuery['and']),
                'not' => array_map(function ($term) {
                    return '*' . trim($term, '*');
                }, $this->parsedQuery['not']),
            ],
            'contains' => [
                'label' => $lang['search_contains'],
                'and' => array_map(function ($term) {
                    return '*' . trim($term, '*') . '*';
                }, $this->parsedQuery['and']),
                'not' => array_map(function ($term) {
                    return '*' . trim($term, '*') . '*';
                }, $this->parsedQuery['not']),
            ]
        ];

        // detect current
        $activeOption = 'custom';
        foreach ($options as $key => $option) {
            if ($this->parsedQuery['and'] === $option['and']) {
                $activeOption = $key;
            }
        }
        if ($activeOption === 'custom') {
            $options = array_merge(['custom' => [
                'label' => $lang['search_custom_match'],
            ]], $options);
        }

        $searchForm->addTagOpen('div')->addClass('toggle');
        // render current
        $currentWrapper = $searchForm->addTagOpen('div')->addClass('current');
        if ($activeOption !== 'exact') {
            $currentWrapper->addClass('changed');
        }
        $searchForm->addHTML($options[$activeOption]['label']);
        $searchForm->addTagClose('div');

        // render options list
        $searchForm->addTagOpen('ul');

        foreach ($options as $key => $option) {
            $listItem = $searchForm->addTagOpen('li');

            if ($key === $activeOption) {
                $listItem->addClass('active');
                $searchForm->addHTML($option['label']);
            } else {
                $this->searchState->addSearchLinkFragment(
                    $searchForm,
                    $option['label'],
                    $option['and'],
                    $option['not']
                );
            }
            $searchForm->addTagClose('li');
        }
        $searchForm->addTagClose('ul');

        $searchForm->addTagClose('div');

        // render options list
    }

    /**
     * Add the elements for the namespace selector
     *
     * @param Form $searchForm
     */
    protected function addNamespaceSelector(Form $searchForm)
    {
        if (!$this->isNamespaceAssistanceAvailable($this->parsedQuery)) {
            return;
        }

        global $lang;

        $baseNS = empty($this->parsedQuery['ns']) ? '' : $this->parsedQuery['ns'][0];
        $extraNS = $this->getAdditionalNamespacesFromResults($baseNS);

        $searchForm->addTagOpen('div')->addClass('toggle');
        // render current
        $currentWrapper = $searchForm->addTagOpen('div')->addClass('current');
        if ($baseNS) {
            $currentWrapper->addClass('changed');
            $searchForm->addHTML('@' . $baseNS);
        } else {
            $searchForm->addHTML($lang['search_any_ns']);
        }
        $searchForm->addTagClose('div');

        // render options list
        $searchForm->addTagOpen('ul');

        $listItem = $searchForm->addTagOpen('li');
        if ($baseNS) {
            $listItem->addClass('active');
            $this->searchState->addSeachLinkNS(
                $searchForm,
                $lang['search_any_ns'],
                ''
            );
        } else {
            $searchForm->addHTML($lang['search_any_ns']);
        }
        $searchForm->addTagClose('li');

        foreach ($extraNS as $ns => $count) {
            $listItem = $searchForm->addTagOpen('li');
            $label = $ns . ($count ? " ($count)" : '');

            if ($ns === $baseNS) {
                $listItem->addClass('active');
                $searchForm->addHTML($label);
            } else {
                $this->searchState->addSeachLinkNS(
                    $searchForm,
                    $label,
                    $ns
                );
            }
            $searchForm->addTagClose('li');
        }
        $searchForm->addTagClose('ul');

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
    protected function addDateSelector(Form $searchForm)
    {
        global $INPUT, $lang;

        $options = [
            'any' => [
                'before' => false,
                'after' => false,
                'label' => $lang['search_any_time'],
            ],
            'week' => [
                'before' => false,
                'after' => '1 week ago',
                'label' => $lang['search_past_7_days'],
            ],
            'month' => [
                'before' => false,
                'after' => '1 month ago',
                'label' => $lang['search_past_month'],
            ],
            'year' => [
                'before' => false,
                'after' => '1 year ago',
                'label' => $lang['search_past_year'],
            ],
        ];
        $activeOption = 'any';
        foreach ($options as $key => $option) {
            if ($INPUT->str('dta') === $option['after']) {
                $activeOption = $key;
                break;
            }
        }

        $searchForm->addTagOpen('div')->addClass('toggle');
        // render current
        $currentWrapper = $searchForm->addTagOpen('div')->addClass('current');
        if ($INPUT->has('dtb') || $INPUT->has('dta')) {
            $currentWrapper->addClass('changed');
        }
        $searchForm->addHTML($options[$activeOption]['label']);
        $searchForm->addTagClose('div');

        // render options list
        $searchForm->addTagOpen('ul');

        foreach ($options as $key => $option) {
            $listItem = $searchForm->addTagOpen('li');

            if ($key === $activeOption) {
                $listItem->addClass('active');
                $searchForm->addHTML($option['label']);
            } else {
                $this->searchState->addSearchLinkTime(
                    $searchForm,
                    $option['label'],
                    $option['after'],
                    $option['before']
                );
            }
            $searchForm->addTagClose('li');
        }
        $searchForm->addTagClose('ul');

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
                    $lastMod = '<span class="search_results__lastmod">' . $lang['lastmod'] . ' ';
                    $lastMod .= '<time datetime="' . date_iso8601($mtime) . '">' . dformat($mtime) . '</time>';
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
        if (!$this->isNamespaceAssistanceAvailable($this->parsedQuery)) {
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
