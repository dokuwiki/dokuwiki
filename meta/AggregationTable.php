<?php

namespace plugin\struct\meta;

/**
 * Creates the table aggregation output
 *
 * @package plugin\struct\meta
 */
class AggregationTable {

    /**
     * @var string the page id of the page this is rendered to
     */
    protected $id;
    /**
     * @var string the Type of renderer used
     */
    protected $mode;
    /**
     * @var \Doku_Renderer the DokuWiki renderer used to create the output
     */
    protected $renderer;
    /**
     * @var SearchConfig the configured search - gives access to columns etc.
     */
    protected $searchConfig;

    /**
     * @var Column[] the list of columns to be displayed
     */
    protected $columns;

    /**
     * @var  Value[][] the search result
     */
    protected $result;

    /**
     * @var int number of all results
     */
    protected $resultCount;

    /**
     * @var array for summing up columns
     */
    protected $sums;

    /**
     * @todo we might be able to get rid of this helper and move this to SearchConfig
     * @var \helper_plugin_struct_config
     */
    protected $helper;

    /**
     * Initialize the Aggregation renderer and executes the search
     *
     * You need to call @see render() on the resulting object.
     *
     * @param string $id
     * @param string $mode
     * @param \Doku_Renderer $renderer
     * @param SearchConfig $searchConfig
     */
    public function __construct($id, $mode, \Doku_Renderer $renderer, SearchConfig $searchConfig) {
        $this->id = $id;
        $this->mode = $mode;
        $this->renderer = $renderer;
        $this->searchConfig = $searchConfig;
        $this->data = $searchConfig->getConf();
        $this->columns = $searchConfig->getColumns();

        $this->result = $this->searchConfig->execute();
        $this->resultCount = $this->searchConfig->getCount();
        $this->helper = plugin_load('helper', 'struct_config');
    }

    /**
     * Create the table on the renderer
     */
    public function render() {
        // table open
        $this->startScope();
        $this->renderActiveFilters();
        $this->renderer->table_open();

        // header
        $this->renderer->tablethead_open();
        $this->renderColumnHeaders();
        $this->renderDynamicFilters();
        $this->renderer->tablethead_close();

        if($this->resultCount) {
            // actual data
            $this->renderResult();

            // footer
            $this->renderSums();
            $this->renderPagingControls();
        } else {
            // nothing found
            $this->renderEmptyResult();
        }

        // table close
        $this->renderer->table_close();
        $this->finishScope();
    }

    /**
     * Adds additional info to document and renderer in XHTML mode
     *
     * @see finishScope()
     */
    protected function startScope() {
        if($this->mode != 'xhtml') return;

        // wrapping div
        $this->renderer->doc .= "<div class=\"structaggregation\">";

        // unique identifier for this aggregation
        $this->renderer->info['struct_table_hash'] = md5(var_export($this->data, true));
    }

    /**
     * Closes the table and anything opened in startScope()
     *
     * @see startScope()
     */
    protected function finishScope() {
        if($this->mode != 'xhtml') return;

        // wrapping div
        $this->renderer->doc .= '</div>';

        // remove identifier from renderer again
        if(isset($this->renderer->info['struct_table_hash'])) {
            unset($this->renderer->info['struct_table_hash']);
        }
    }

    /**
     * Displays info about the currently applied filters
     */
    protected function renderActiveFilters() {
        if($this->mode != 'xhtml') return;
        $dynamic = $this->searchConfig->getDynamicParameters();
        $filters = $dynamic->getFilters();
        if(!$filters) return;

        $fltrs = array();
        foreach($filters as $column => $filter) {
            list($comp, $value) = $filter;
            $fltrs[] = $column . ' ' . $comp . ' ' . $value;
        }

        $this->renderer->doc .= '<div class="filter">';
        $this->renderer->doc .= '<h4>' . sprintf($this->helper->getLang('tablefilteredby'), hsc(implode(' & ', $fltrs))) . '</h4>';
        $this->renderer->doc .= '<div class="resetfilter">';
        $this->renderer->internallink($this->id, $this->helper->getLang('tableresetfilter'));
        $this->renderer->doc .= '</div>';
        $this->renderer->doc .= '</div>';
    }

    /**
     * Shows the column headers with links to sort by column
     */
    protected function renderColumnHeaders() {
        $this->renderer->tablerow_open();

        // additional column for row numbers
        if($this->data['rownumbers']) {
            $this->renderer->tableheader_open();
            $this->renderer->cdata('#');
            $this->renderer->tableheader_close();
        }

        // show all headers
        foreach($this->data['headers'] as $num => $header) {
            if(!isset($this->columns[$num])) break; // less columns where available then expected
            $column = $this->columns[$num];

            // use field label if no header was set
            if(blank($header)) {
                if(is_a($column, 'plugin\struct\meta\PageColumn')) {
                    $header = $this->helper->getLang('pagelabel'); // @todo this could be part of PageColumn::getTranslatedLabel
                } else if(is_a($column, 'plugin\struct\meta\Column')) {
                    $header = $column->getTranslatedLabel();
                } else {
                    $header = 'column ' . $num; // this should never happen
                }
            }

            // simple mode first
            if($this->mode != 'xhtml') {
                $this->renderer->tableheader_open();
                $this->renderer->cdata($header);
                $this->renderer->tableheader_close();
                continue;
            }

            // still here? create custom header for more flexibility

            // width setting
            $width = '';
            if(isset($data['widths'][$num]) && $data['widths'][$num] != '-') {
                $width = ' style="width: ' . $data['widths'][$num] . ';"';
            }

            // sort indicator and link
            $sortclass = '';
            $sorts = $this->searchConfig->getSorts();
            $dynamic = $this->searchConfig->getDynamicParameters();
            $dynamic->setSort($column, true);
            if(isset($sorts[$column->getFullQualifiedLabel()])) {
                list(/*colname*/, $currentSort) = $sorts[$column->getFullQualifiedLabel()];
                if($currentSort) {
                    $sortclass = 'sort-down';
                    $dynamic->setSort($column, false);
                } else {
                    $sortclass = 'sort-up';
                }
            }
            $link = wl($this->id, $dynamic->getURLParameters());

            // output XHTML header
            $this->renderer->doc .= "<th $width >";
            $this->renderer->doc .= '<a href="' . $link . '" class="' . $sortclass . '" title="' . $this->helper->getLang('sort') . '">' . hsc($header) . '</a>';
            $this->renderer->doc .= '</th>';
        }

        $this->renderer->tablerow_close();
    }

    /**
     * Add input fields for dynamic filtering
     */
    protected function renderDynamicFilters() {
        if($this->mode != 'xhtml') return;
        if(!$this->data['dynfilters']) return;
        global $conf;

        $this->renderer->doc .= '<tr class="dataflt">';

        // add extra column for row numbers
        if($this->data['rownumbers']) {
            $this->renderer->doc .= '<th></th>';
        }

        // each column gets a form
        foreach($this->columns as $column) {
            $this->renderer->doc .= '<th>';
            {
                $form = new \Doku_Form(array('method' => 'GET', 'action' => wl($this->id)));
                unset($form->_hidden['sectok']); // we don't need it here
                if(!$conf['userewrite']) $form->addHidden('id', $this->id);

                // current value
                $dynamic = $this->searchConfig->getDynamicParameters();
                $filters = $dynamic->getFilters();
                if(isset($filters[$column->getFullQualifiedLabel()])) {
                    list(, $current) = $filters[$column->getFullQualifiedLabel()];
                    $dynamic->removeFilter($column);
                } else {
                    $current = '';
                }

                // Add current request params
                $params = $dynamic->getURLParameters();
                foreach($params as $key => $val) {
                    $form->addHidden($key, $val);
                }

                // add input field
                $key = $column->getFullQualifiedLabel() . '*~';
                $form->addElement(form_makeField('text', SearchConfigParameters::$PARAM_FILTER . '[' . $key . ']', $current, ''));
                $this->renderer->doc .= $form->getForm();
            }
            $this->renderer->doc .= '</th>';
        }
        $this->renderer->doc .= '</tr>';

    }

    /**
     * Display the actual table data
     */
    protected function renderResult() {
        $this->renderer->tabletbody_open();
        foreach($this->result as $rownum => $row) {
            $this->renderer->tablerow_open();

            // row number column
            if($this->data['rownumbers']) {
                $this->renderer->tablecell_open();
                $this->renderer->doc .= $rownum + 1;
                $this->renderer->tablecell_close();
            }

            /** @var Value $value */
            foreach($row as $colnum => $value) {
                $this->renderer->tablecell_open();
                $value->render($this->renderer, $this->mode);
                $this->renderer->tablecell_close();

                // summarize
                if($this->data['summarize'] && is_numeric($value->getValue())) {
                    if(!isset($this->sums[$colnum])) {
                        $this->sums[$colnum] = 0;
                    }
                    $this->sums[$colnum] += $value->getValue();
                }
            }
            $this->renderer->tablerow_close();
        }
        $this->renderer->tabletbody_close();
    }

    /**
     * Renders an information row for when no results were found
     */
    protected function renderEmptyResult() {
        $this->renderer->tablerow_open();
        $this->renderer->tablecell_open(count($this->data['cols']) + $this->data['rownumbers'], 'center');
        $this->renderer->cdata($this->helper->getLang('none'));
        $this->renderer->tablecell_close();
        $this->renderer->tablerow_close();
    }

    /**
     * Add sums if wanted
     */
    protected function renderSums() {
        if(empty($this->data['summarize'])) return;

        $this->renderer->tablerow_open();
        $len = count($this->data['cols']);

        if($this->data['rownumbers']) {
            $this->renderer->tablecell_open();
            $this->renderer->tablecell_close();
        }

        for($i = 0; $i < $len; $i++) {
            $this->renderer->tablecell_open(1, $this->data['align'][$i]);
            if(!empty($sums[$i])) {
                $this->renderer->cdata('∑ ' . $sums[$i]);
            } else {
                if($this->mode == 'xhtml') {
                    $this->renderer->doc .= '&nbsp;';
                }
            }
            $this->renderer->tablecell_close();
        }
        $this->renderer->tablerow_close();
    }

    /**
     * Adds paging controls to the table
     */
    protected function renderPagingControls() {
        if(empty($this->data['limit'])) return;
        if($this->mode != 'xhtml') ;

        $this->renderer->tablerow_open();
        $this->renderer->tableheader_open((count($this->data['cols']) + ($this->data['rownumbers'] ? 1 : 0)));
        $offset = $this->data['offset'];

        // prev link
        if($offset) {
            $prev = $offset - $this->data['limit'];
            if($prev < 0) {
                $prev = 0;
            }

            $dynamic = $this->searchConfig->getDynamicParameters();
            $dynamic->setOffset($prev);
            $link = wl($this->id, $dynamic->getURLParameters());
            $this->renderer->doc .= '<a href="' . $link . '" class="prev">' . $this->helper->getLang('prev') . '</a>';
        }

        // next link
        if($this->resultCount > $offset + $this->data['limit']) {
            $next = $offset + $this->data['limit'];
            $dynamic = $this->searchConfig->getDynamicParameters();
            $dynamic->setOffset($next);
            $link = wl($this->id, $dynamic->getURLParameters());
            $this->renderer->doc .= '<a href="' . $link . '" class="next">' . $this->helper->getLang('next') . '</a>';
        }

        $this->renderer->tableheader_close();
        $this->renderer->tablerow_close();
    }
}
