<?php

namespace dokuwiki\plugin\struct\meta;


/**
 * Class AggregationList
 *
 * @package dokuwiki\plugin\struct\meta
 */
class AggregationList {

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
    protected $resultColumnCount;

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
        $this->resultColumnCount = count($this->columns);
        $this->resultPIDs = $this->searchConfig->getPids();
    }

    /**
     * Create the list on the renderer
     */
    public function render() {

        $this->startScope();

        $this->renderer->listu_open();

        foreach ($this->result as $result) {
            $this->renderer->listitem_open(1);
            $this->renderer->listcontent_open();
            $this->renderListItem($result);
            $this->renderer->listcontent_close();
            $this->renderer->listitem_close();
        }

        $this->renderer->listu_close();

        $this->finishScope();

        return;
    }

    /**
     * Adds additional info to document and renderer in XHTML mode
     *
     * @see finishScope()
     */
    protected function startScope() {
        // wrapping div
        if($this->mode != 'xhtml') return;
        $this->renderer->doc .= "<div class=\"structaggregation listaggregation\">";
    }

    /**
     * Closes anything opened in startScope()
     *
     * @see startScope()
     */
    protected function finishScope() {
        // wrapping div
        if($this->mode != 'xhtml') return;
        $this->renderer->doc .= '</div>';
    }

    /**
     * @param $resultrow
     */
    protected function renderListItem($resultrow) {
        $sepbyheaders = $this->searchConfig->getConf()['sepbyheaders'];
        $headers = $this->searchConfig->getConf()['headers'];

        /**
         * @var Value $value
         */
        foreach ($resultrow as $column => $value) {
            if ($value->isEmpty()) {
                continue;
            }
            if ($sepbyheaders && !empty($headers[$column])) {
                if ($this->mode == 'xhtml') {
                    $this->renderer->doc .= '<span class="struct_header">' . hsc($headers[$column]) . '</span>';
                } else {
                    $this->renderer->cdata($headers[$column]);
                }
            }
            if ($this->mode == 'xhtml') {
                $type = 'struct_' . strtolower($value->getColumn()->getType()->getClass());
                $this->renderer->doc .= '<div class="' . $type . '">';
            }
            $value->render($this->renderer, $this->mode);
            if ($column < $this->resultColumnCount) {
                $this->renderer->doc .= ' ';
            }
            if ($this->mode == 'xhtml') {
                $this->renderer->doc .= '</div>';
            }
        }

    }
}
