<?php

namespace dokuwiki\Ui;

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

        $searchHTML .= $this->getSearchIntroHTML($this->query);

        $searchHTML .= $this->getPageLookupHTML($this->pageLookupResults);

        $searchHTML .= $this->getFulltextResultsHTML($this->fullTextResults, $this->highlight);

        echo $searchHTML;
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
            $html .= '<li> ';
            if (useHeading('navigation')) {
                $name = $title;
            } else {
                $ns = getNS($id);
                if ($ns) {
                    $name = shorten(noNS($id), ' (' . $ns . ')', 30);
                } else {
                    $name = $id;
                }
            }
            $html .= html_wikilink(':' . $id, $name);
            $html .= '</li> ';
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
            $html .= '<dt>';
            $html .= html_wikilink(':' . $id, useHeading('navigation') ? null : $id, $highlight);
            if ($cnt !== 0) {
                $html .= ': ' . $cnt . ' ' . $lang['hits'] . '';
            }
            $html .= '</dt>';
            if ($cnt !== 0) {
                if ($num < FT_SNIPPET_NUMBER) { // create snippets for the first number of matches only
                    $html .= '<dd>' . ft_snippet($id, $highlight) . '</dd>';
                }
                $num++;
            }
        }
        $html .= '</dl>';

        return $html;
    }
}
