<?php

namespace dokuwiki\Ui;

use dokuwiki\Form\Form;

class SearchState
{
    /**
     * @var array
     */
    protected $parsedQuery = [];

    public function __construct(array $parsedQuery)
    {
        global $INPUT;

        $this->parsedQuery = $parsedQuery;
        $this->parsedQuery['after'] = $INPUT->str('after');
        $this->parsedQuery['before'] = $INPUT->str('before');
        $this->parsedQuery['sort'] = $INPUT->str('sort');
    }

    /**
     * Add a link to the form which limits the search to the provided namespace
     *
     * @param Form   $searchForm
     * @param string $label
     * @param string $ns namespace to which to limit the search, empty string to remove namespace limitation
     */
    public function addSeachLinkNS(Form $searchForm, $label, $ns)
    {
        $parsedQuery = $this->parsedQuery;
        $parsedQuery['notns'] = [];
        $parsedQuery['ns'] = $ns ? [$ns] : [];
        $this->addSearchLink($searchForm, $label, $parsedQuery);
    }

    /**
     * Add a link to the form which searches only for the provided words, but keeps the namespace and time limitations
     *
     * @param Form   $searchForm
     * @param string $label
     * @param array  $and
     * @param array  $not
     */
    public function addSearchLinkFragment(Form $searchForm, $label, array $and, array $not)
    {
        $parsedQuery = $this->parsedQuery;
        $parsedQuery['and'] = $and;
        $parsedQuery['not'] = $not;
        $this->addSearchLink($searchForm, $label, $parsedQuery);
    }

    /**
     * Add a link to the form which modifies the current search's time limitations
     *
     * @param Form        $searchForm
     * @param string      $label
     * @param string      $after
     * @param null|string $before
     */
    public function addSearchLinkTime(Form $searchForm, $label, $after, $before = null)
    {
        $parsedQuery = $this->parsedQuery;
        $parsedQuery['after'] = $after;
        $parsedQuery['before'] = $before;

        $this->addSearchLink($searchForm, $label, $parsedQuery);
    }

    /**
     * Add a link to the form which sets the sort preference for the current search
     *
     * @param Form $searchForm
     * @param string $label
     * @param string $sort
     */
    public function addSearchLinkSort(Form $searchForm, $label, $sort)
    {
        $parsedQuery = $this->parsedQuery;
        $parsedQuery['sort'] = $sort;

        $this->addSearchLink($searchForm, $label, $parsedQuery);
    }

    protected function addSearchLink(
        Form $searchForm,
        $label,
        $parsedQuery
    ) {
        global $ID;

        $newQuery = ft_queryUnparser_simple(
            $parsedQuery['and'],
            $parsedQuery['not'],
            $parsedQuery['phrases'],
            $parsedQuery['ns'],
            $parsedQuery['notns']
        );
        $hrefAttributes = ['do' => 'search', 'searchPageForm' => '1', 'q' => $newQuery];
        if ($parsedQuery['after']) {
            $hrefAttributes['after'] = $parsedQuery['after'];
        }
        if ($parsedQuery['before']) {
            $hrefAttributes['before'] = $parsedQuery['before'];
        }
        if ($parsedQuery['sort']) {
            $hrefAttributes['sort'] = $parsedQuery['sort'];
        }
        $searchForm->addTagOpen('a')
            ->attrs([
                'href' => wl($ID, $hrefAttributes, false, '&')
            ]);
        $searchForm->addHTML($label);
        $searchForm->addTagClose('a');
    }
}
