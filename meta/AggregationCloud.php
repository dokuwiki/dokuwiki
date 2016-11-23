<?php
namespace dokuwiki\plugin\struct\meta;
class AggregationCloud {
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
     * @var string[] the result PIDs for each row
     */
    protected $resultPIDs;
    /**
     * @var array for summing up columns
     */
    protected $sums;
    /**
     * @var bool skip full table when no results found
     */
    protected $simplenone = true;
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
        $this->result = $this->search(); //$this->searchConfig->execute();
        //$this->resultCount = $this->searchConfig->getCount();
        //$this->resultPIDs = $this->searchConfig->getPids();
        $this->helper = plugin_load('helper', 'struct_config');
    }

    public function search() {
        $QB = new QueryBuilder;
        $schema = $this->data['schemas'][0][0];
        $colref = $this->columns[0]->getColref();
        if ($this->columns[0]->getType()->isMulti()) {
            $table = 'multi_' . $schema;
            $col = 'value';
            $QB->filters()->whereAnd('T1.colref='.$colref);
        } else {
            $table = 'data_' . $schema;
            $col = 'col' . $colref;
        }
        $QB->addTable($table, 'T1');
        $QB->addSelectColumn('T1', $col, 'tag');
        $QB->addSelectStatement("COUNT(T1.$col)", 'count');
        $QB->filters()->whereAnd('T1.latest=1');
        $QB->addGroupByStatement('tag');
        $QB->addOrderBy('tag');
        /*
          if ($min=$this->data['min']) {
          $QB->filters()->whereAnd("count > $min");
          }
        */
        $sql = $QB->getSQL();
        $db = plugin_load('helper', 'struct_db')->getDB();
        $res=$db->query($sql[0], $sql[1]);
        $results = $db->res2arr($res);
        $db->res_close($res);
        foreach ($results as &$result) {
            $result['tag'] = new Value($this->columns[0], $result['tag']);
        }
        return $results;
    }

    /**
     * Create the table on the renderer
     */
    public function render() {
        $this->startScope();
        $this->renderer->doc .= '<ul>';
        foreach ($this->result as $result) {
            $this->renderResult($result);
        }
        $this->renderer->doc .= '</ul>';
        $this->finishScope();
        return;
    }
    /**
     * Adds additional info to document and renderer in XHTML mode
     *
     * @see finishScope()
     */
    protected function startScope() {
        // unique identifier for this aggregation
        $this->renderer->info['struct_cloud_hash'] = md5(var_export($this->data, true));
        // wrapping div
        if($this->mode != 'xhtml') return;
        $this->renderer->doc .= "<div class=\"structaggregation cloudaggregation\">";
    }
    /**
     * Closes the table and anything opened in startScope()
     *
     * @see startScope()
     */
    protected function finishScope() {
        // remove identifier from renderer again
        if(isset($this->renderer->info['struct_cloud_hash'])) {
            unset($this->renderer->info['struct_cloud_hash']);
        }
        // wrapping div
        if($this->mode != 'xhtml') return;
        $this->renderer->doc .= '</div>';
    }
    protected function renderResult($result) {
        /**
         * @var Value $value
         */
        $value = $result['tag'];
        $count = $result['count'];
        if ($value->isEmpty()) {
            return;
        }

        $raw = $value->getRawValue();
        if (is_array($raw)) {
            $raw = $raw[0];
        }
        $schema = $this->data['schemas'][0][0];
        $col = $value->getColumn()->getLabel();
        $this->renderer->doc .= '<li><div class="li">';
        $this->renderer->doc .= '<div data-count="'. $count.'" class="' . $value->getColumn()->getLabel() . '">';
        //$value->render($this->renderer, $this->mode);
        $this->renderer->internallink("?flt[$schema.$col*~]=" . urlencode($raw),$raw);
        if ($column < $this->resultCount) {
            $this->renderer->doc .= ' ';
        }
        $this->renderer->doc .= '</div>';
        $this->renderer->doc .= '</div></li>';
    }
}