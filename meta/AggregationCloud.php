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

        $this->sortResults();

        $this->startScope();
        $this->startList();
        foreach ($this->result as $result) {
            $this->renderTag($result);
        }
        $this->finishList();
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
        if ($value->isEmpty()) {
            return;
        }

        $this->renderer->listitem_open(1);
        $this->renderer->listcontent_open();

        $this->renderTagLink($value, $count);
        $this->renderer->listcontent_close();
        $this->renderer->listitem_close();
    }

    /**
     * @param Value $value
     * @param int $count
     */
    protected function renderTagLink(Value $value, $count) {
        $type = strtolower($value->getColumn()->getType()->getClass());
        $weight = $this->getWeight($count, $this->min, $this->max);
        $schema = $this->data['schemas'][0][0];
        $col = $value->getColumn()->getLabel();

        if (!empty($this->data['target'])) {
            $target = $this->data['target'];
        } else {
            global $INFO;
            $target = $INFO['id'];
        }

        $tagValue = $value->getDisplayValue();
        if (empty($tagValue)) {
            $tagValue = $value->getRawValue();
        }
        if (is_array($tagValue)) {
            $tagValue = $tagValue[0];
        }
        $filter = "flt[$schema.$col*~]=" . urlencode($tagValue);
        $linktext = $tagValue;


        if($this->mode != 'xhtml') {
            $this->renderer->internallink("$target?$filter",$linktext);
            return;
        }

        $this->renderer->doc .= "<div style='font-size:$weight%' data-count='$count' class='cloudtag struct_$type'>";

        if ($type == 'color') {
            $url = wl($target, $filter);
            $style = "background-color:$tagValue;display:block;height:100%";
            $this->renderer->doc .=  "<a href='$url' style='$style'></a>";
        } else {
            if ($type == 'media' && $value->getColumn()->getType()->getConfig()['mime'] == 'image/') {
                $linktext = p_get_instructions("[[|{{{$tagValue}?$weight}}]]")[2][1][1];
            }

            $this->renderer->internallink("$target?$filter", $linktext);
        }
        $this->renderer->doc .= '</div>';
    }

    /**
     * This interpolates the weight between 70 and 150 based on $min, $max and $current
     *
     * @param int $current
     * @param int $min
     * @param int $max
     * @return int
     */
    protected function getWeight($current, $min, $max) {
        if ($min == $max) {
            return 100;
        }
        return round(($current - $min)/($max - $min) * 80 + 70);
    }

    /**
     * Sort the list of results
     */
    protected function sortResults() {
        foreach ($this->result as &$result) {
            if ($result['tag']->getColumn()->getType()->getClass() == 'Color') {
                $result['sort'] = $this->getHue($result['tag']->getRawValue());
            } else {
                $result['sort'] = $result['tag']->getDisplayValue();
            }
        }
        usort($this->result, function ($a, $b) {
            if ($a['sort'] < $b['sort']) {
                return -1;
            }
            if ($a['sort'] > $b['sort']) {
                return 1;
            }
            return 0;
        });
    }

    /**
     * Calculate the hue of a color to use it for sorting so we can sort similar colors together.
     *
     * @param string $color the color as #RRGGBB
     * @return float|int
     */
    protected function getHue($color) {
        if (!preg_match('/^#[0-9A-F]{6}$/i', $color)) {
            return 0;
        }

        $red   = hexdec(substr($color, 1, 2));
        $green = hexdec(substr($color, 3, 2));
        $blue  = hexdec(substr($color, 5, 2));

        $min = min([$red, $green, $blue]);
        $max = max([$red, $green, $blue]);

        if ($max == $red) {
            $hue = ($green-$blue)/($max-$min);
        }
        if ($max == $green) {
            $hue = 2 + ($blue-$red)/($max-$min);
        }
        if ($max == $blue) {
            $hue = 4 + ($red-$green)/($max-$min);
        }
        $hue = $hue * 60;
        if ($hue < 0) {
            $hue += 360;
        }
        return $hue;
    }

    protected function startList() {
        $this->renderer->listu_open();
    }

    protected function finishList() {
        $this->renderer->listu_close();
    }
}
