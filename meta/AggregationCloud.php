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
     * Initialize the Aggregation renderer and executes the search
     *
     * You need to call @see render() on the resulting object.
     *
     * @param string $id
     * @param string $mode
     * @param \Doku_Renderer $renderer
     * @param SearchConfig $searchConfig
     */
    public function __construct($id, $mode, \Doku_Renderer $renderer, SearchCloud $searchConfig) {
        $this->id = $id;
        $this->mode = $mode;
        $this->renderer = $renderer;
        $this->searchConfig = $searchConfig;
        $this->data = $searchConfig->getConf();
        $this->columns = $searchConfig->getColumns();
        $this->result = $this->searchConfig->execute();
        $this->resultCount = $this->searchConfig->getCount();

        $this->max = $this->result[0]['count'];
        $this->min = end($this->result)['count'];
    }

    /**
     * Create the table on the renderer
     */
    public function render() {
        $this->startScope();
        $this->renderer->doc .= '<ul>';
        foreach ($this->result as $result) {
            $this->renderTag($result);
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
        // wrapping div
        if($this->mode != 'xhtml') return;
        $this->renderer->doc .= "<div class=\"structcloud\">";
    }

    /**
     * Closes the table and anything opened in startScope()
     *
     * @see startScope()
     */
    protected function finishScope() {
        // wrapping div
        if($this->mode != 'xhtml') return;
        $this->renderer->doc .= '</div>';
    }

    /**
     * Render a tag of the cloud
     *
     * @param ['tag' => Value, 'count' => int] $result
     */
    protected function renderTag($result) {
        /**
         * @var Value $value
         */
        $value = $result['tag'];
        $count = $result['count'];
        $weight = $this->getWeight($count, $this->min, $this->max);
        $type = 'struct_' . strtolower($value->getColumn()->getType()->getClass());
        if ($value->isEmpty()) {
            return;
        }

        $tagValue = $value->getDisplayValue();
        if (empty($tagValue)) {
            $tagValue = $value->getRawValue();
        }
        if (is_array($tagValue)) {
            $tagValue = $tagValue[0];
        }
        $schema = $this->data['schemas'][0][0];
        $col = $value->getColumn()->getLabel();
        $this->renderer->doc .= '<li><div class="li">';
        $this->renderer->doc .= "<div style='font-size:$weight%' data-count='$count' class='cloudtag $type'>";

        $this->renderer->internallink("?flt[$schema.$col*~]=" . urlencode($tagValue),$tagValue);
        $this->renderer->doc .= '</div>';
        $this->renderer->doc .= '</div></li>';
    }

    /**
     * This interpolates the weight between 70 and 150 based on $min, $max and $current
     *
     * @param int $current
     * @param int $min
     * @param int $max
     * @return float|int
     */
    protected function getWeight($current, $min, $max) {
        if ($min == $max) {
            return 100;
        }
        return ($current - $min)/($max - $min) * 80 + 70;
    }
}
