<?php
/**
 * DokuWiki Plugin struct (Syntax Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Andreas Gohr, Michael Große <dokuwiki@cosmocode.de>
 */

// must be run within Dokuwiki
use plugin\struct\meta\Column;
use plugin\struct\meta\ConfigParser;
use plugin\struct\meta\Search;
use plugin\struct\meta\SearchConfig;
use plugin\struct\meta\SearchException;
use plugin\struct\meta\StructException;

if (!defined('DOKU_INC')) die();

class syntax_plugin_struct_table extends DokuWiki_Syntax_Plugin {
    /**
     * @return string Syntax mode type
     */
    public function getType() {
        return 'substition';
    }
    /**
     * @return string Paragraph type
     */
    public function getPType() {
        return 'block';
    }
    /**
     * @return int Sort order - Low numbers go before high numbers
     */
    public function getSort() {
        return 155;
    }

    /**
     * Connect lookup pattern to lexer.
     *
     * @param string $mode Parser mode
     */
    public function connectTo($mode) {
        $this->Lexer->addSpecialPattern('----+ *struct table *-+\n.*?\n----+', $mode, 'plugin_struct_table');
    }


    /**
     * Handle matches of the struct syntax
     *
     * @param string $match The match of the syntax
     * @param int    $state The state of the handler
     * @param int    $pos The position in the document
     * @param Doku_Handler    $handler The handler
     * @return array Data for the renderer
     */
    public function handle($match, $state, $pos, Doku_Handler $handler){

        $lines = explode("\n", $match);
        array_shift($lines);
        array_pop($lines);

        try {
            $parser = new ConfigParser($lines);
            return  $parser->getConfig();
        } catch (StructException $e) {
            msg($e->getMessage(), -1, $e->getLine(), $e->getFile());
            return null;
        }
    }

    protected $sums = array();

    /**
     * Render xhtml output or metadata
     *
     * @param string         $mode      Renderer mode (supported modes: xhtml)
     * @param Doku_Renderer  $renderer  The renderer
     * @param array          $data      The data from the handler() function
     * @return bool If rendering was successful.
     */
    public function render($mode, Doku_Renderer $renderer, $data) {
        if($mode != 'xhtml') return false;
        if(!$data) return false;

        //reset counters
        $this->sums = array();

        try {
            $search = new SearchConfig($data);
            $data = $search->getConf();
            $rows = $search->execute();
            $cnt = $search->getCount();
            $cols = $search->getColumns();

            if ($cnt === 0) {
                $this->nullList($data, $mode, $renderer);
                return true;
            }

            $this->renderPreTable($mode, $renderer, $data, $cols);
            $this->renderRows($mode, $renderer, $data, $rows);
            $this->renderPostTable($mode, $renderer, $data, $cnt);
        } catch (StructException $e) {
            msg($e->getMessage(), -1, $e->getLine(), $e->getFile());
        }

        return true;
    }

    /**
     * create the pretext to the actual table rows
     *
     * @param string $mode
     * @param Doku_Renderer $renderer
     * @param array $data the configuration data
     * @param Column[] $cols
     */
    protected function renderPreTable($mode, Doku_Renderer $renderer, $data, $cols) {
        $this->startScope($mode, $renderer, md5(serialize($data)));
        $this->showActiveFilters($mode, $renderer);
        $this->startTable($mode, $renderer);
        $renderer->tablethead_open();
        $this->buildColumnHeaders($mode, $renderer, $data, $cols);
        $this->addDynamicFilters($mode, $renderer, $data);
        $renderer->tablethead_close();
    }

    /**
     * @param array $data
     * @param int $rowcnt
     *
     * @return string
     */
    private function renderPostTable($mode, Doku_Renderer $renderer, $data, $rowcnt) {
        $this->summarize($mode, $renderer, $data, $this->sums);
        $this->addLimitControls($mode, $renderer, $data, $rowcnt);
        $this->finishTableAndScope($mode, $renderer);
    }

    /**
     * if limit was set, add control
     *
     * @param string        $mode     the mode of the renderer
     * @param Doku_Renderer $renderer the renderer
     * @param array         $data     the configuration of the table/search
     * @param               $rowcnt
     */
    protected function addLimitControls($mode, Doku_Renderer $renderer, $data, $rowcnt) {
        global $ID;

        if($data['limit']) {
            $renderer->tablerow_open();
            $renderer->tableheader_open((count($data['cols']) + ($data['rownumbers'] ? 1 : 0)));
            $offset = (int) $_REQUEST['dataofs'];

            // keep url params
            $params = array();
            if (!empty($data['current_params']['dataflt'])) {$params['dataflt'] = $data['current_params']['dataflt'];}
            if (!empty($data['current_params']['datasrt'])) {$params['datasrt'] = $data['current_params']['datasrt'];}

            if($offset) {
                $prev = $offset - $data['limit'];
                if($prev < 0) {
                    $prev = 0;
                }
                $params['dataofs'] = $prev;
                $renderer->internallink($ID . '?' . http_build_query($params), $this->getLang('prev'));
            }

            if($rowcnt > $offset + $data['limit']) {
                $next = $offset + $data['limit'];
                $params['dataofs'] = $next;
                $renderer->internallink($ID . '?' . http_build_query($params), $this->getLang('next'));
            }
            $renderer->tableheader_close();
            $renderer->tablerow_close();
        }
    }

    /**
     * @param string        $mode     the mode of the renderer
     * @param Doku_Renderer $renderer the renderer
     */
    protected function showActiveFilters($mode, Doku_Renderer $renderer) {
        global $ID;

        if($mode == 'xhtml' && !empty($data['current_params']['dataflt'])) {
            $filters = $data['current_params']['dataflt'];
            $confHelper = $this->loadHelper('struct_config');
            $fltrs = array();
            foreach($filters as $colcomp => $filter) {
                $filter = $confHelper->parseFilterLine('', $colcomp.$filter);
                if(strpos($filter[1], '~') !== false) {
                    if(strpos($filter[1], '!~') !== false) {
                        $comparator_value = '!~' . str_replace('%', '*', $filter[2]);
                    } else {
                        $comparator_value = '~' . str_replace('%', '', $filter[2]);
                    }
                    $fltrs[] = $filter[0] . $comparator_value;
                } else {
                    $fltrs[] = $filter[0] . $filter[1] . $filter[2];
                }
            }

            $renderer->doc .= '<div class="filter">';
            $renderer->doc .= '<h4>' . sprintf($this->getLang('tablefilteredby'), hsc(implode(' & ', $fltrs))) . '</h4>';
            $renderer->doc .= '<div class="resetfilter">';
            $renderer->internallink($ID, $this->getLang('tableresetfilter'));
            $renderer->doc .=  '</div>';
            $renderer->doc .= '</div>';
        }
    }

    /**
     * @param string        $mode     the mode of the renderer
     * @param Doku_Renderer $renderer the renderer
     * @param array         $data     the configuration of the table/search
     */
    protected function addDynamicFilters($mode, Doku_Renderer $renderer, $data) {
        if ($mode != 'xhtml') return;

        global $conf, $ID;

        $cur_params = $data['current_params'];
        $html = '';
        if($data['dynfilters']) {
            $html .= '<tr class="dataflt">';

            if($data['rownumbers']) {
                $html .= '<th></th>';
            }

            foreach($data['headers'] as $num => $head) {
                $html .= '<th>';
                $form = new Doku_Form(array('method' => 'GET',));
                $form->_hidden = array();
                if(!$conf['userewrite']) {
                    $form->addHidden('id', $ID);
                }

                $key = $data['cols'][$num] . '*~';
                $val = isset($cur_params['dataflt'][$key]) ? $cur_params['dataflt'][$key] : '';

                // Add current request params
                if (!empty($cur_params['datasrt'])) {
                    $form->addHidden('datasrt', $cur_params['datasrt']);
                }
                if (!empty($cur_params['dataofs'])) {
                    $form->addHidden('dataofs', $cur_params['dataofs']);
                }
                foreach($cur_params['dataflt'] as $c_key => $c_val) {
                    if($c_val !== '' && $c_key !== $key) {
                        $form->addHidden('dataflt[' . $c_key . ']', $c_val);
                    }
                }

                $form->addElement(form_makeField('text', 'dataflt[' . $key . ']', $val, ''));
                $html .= $form->getForm();
                $html .= '</th>';
            }
            $html .= '</tr>';
            $renderer->doc .= $html;
        }
    }

    /**
     * @param string        $mode     the mode of the renderer
     * @param Doku_Renderer $renderer the renderer
     */
    private function startTable($mode, Doku_Renderer $renderer) {
        $renderer->table_open();
    }

    /**
     * @param string $mode the mode of the renderer
     * @param Doku_Renderer $renderer the renderer
     * @param array $data the configuration of the table/search
     * @param Column[] $cols
     */
    protected function buildColumnHeaders($mode, Doku_Renderer $renderer, $data, $cols) {
        global $ID;

        $renderer->tablerow_open();

        if($data['rownumbers']) {
            $renderer->tableheader_open();
            $renderer->cdata('#');
            $renderer->tableheader_close();
        }

        foreach($data['headers'] as $num => $head) {
            $ckey = $data['cols'][$num];
            if(blank($head)) {
                $head = $cols[$num]->getTranslatedLabel();
            }

            $width = '';
            if(isset($data['widths'][$num]) AND $data['widths'][$num] != '-') {
                $width = ' style="width: ' . $data['widths'][$num] . ';"';
            }
            if ($mode == 'xhmtl') {
                $renderer->doc .= '<th' . $width . '>';
            } else {
                $renderer->tableheader_open();
            }

            // add sort arrow
            if ($mode == 'xhtml') {
                if(isset($data['sort']) && $ckey == $data['sort'][0]) {
                    if($data['sort'][1] == 'ASC') {
                        $renderer->doc .= '<span>&darr;</span> ';
                        $ckey = '^' . $ckey;
                    } else {
                        $renderer->doc .= '<span>&uarr;</span> ';
                    }
                }
            }
            $renderer->internallink($ID . "?" . http_build_query(array('datasrt' => $ckey,) + $data['current_params']), hsc($head));
            $renderer->tableheader_close();
        }
        $renderer->tablerow_close();
    }

    /**
     * @param string        $mode     the mode of the renderer
     * @param Doku_Renderer $renderer the renderer
     * @param string        $hash     hash to identify the table and group images in gallery
     */
    protected function startScope($mode, \Doku_Renderer $renderer, $hash) {
        if ($mode == 'xhtml') {
            $renderer->doc .= "<div class=\"table structaggregation\">";
            $renderer->info['struct_table_hash'] = $hash;
        }
    }

    /**
     * if summarize was set, add sums
     *
     * @param string        $mode     the mode of the renderer
     * @param Doku_Renderer $renderer the renderer
     * @param array         $data     the configuration of the table/search
     * @param array         $sums     the summarized output of the numerical fields
     */
    private function summarize($mode, \Doku_Renderer $renderer, $data, $sums) {
        if($data['summarize']) {
            $renderer->tablerow_open();
            $len = count($data['cols']);

            if($data['rownumbers']) {
                $renderer->tablecell_open();
                $renderer->tablecell_close();
            }

            for($i = 0; $i < $len; $i++) {
                $renderer->tablecell_open(1, $data['align'][$i]);
                if(!empty($sums[$i])) {
                    $renderer->cdata('∑ ' . $sums[$i]);
                } else {
                    if ($mode == 'xhtml') {
                        $renderer->doc .= '&nbsp;';
                    }
                }
                $renderer->tablecell_close();
            }
            $renderer->tablerow_close();
        }
    }

    /**
     * @param string        $mode     the mode of the renderer
     * @param Doku_Renderer $renderer the renderer
     *
     */
    private function finishTableAndScope($mode, Doku_Renderer $renderer) {
        $renderer->table_close();
        if ($mode == 'xhtml') {
            $renderer->doc .= '</div>';
            if(isset($renderer->info['struct_table_hash'])) {
                unset($renderer->info['struct_table_hash']);
            }
        }
    }

    /**
     * @param string        $mode     the mode of the renderer
     * @param Doku_Renderer $renderer the renderer
     * @param array         $data     the configuration of the table/search
     * @param               $rows
     *
     */
    private function renderRows($mode, Doku_Renderer $renderer, $data, $rows) {
        $renderer->tabletbody_open();
        foreach($rows as $rownum => $row) {
            $renderer->tablerow_open();

            if($data['rownumbers']) {
                $renderer->tablecell_open();
                $renderer->doc .= $rownum + 1;
                $renderer->tablecell_close();
            }

            /** @var plugin\struct\meta\Value $value */
            foreach($row as $colnum => $value) {
                $renderer->tablecell_open();
                $value->render($renderer, $mode);
                $renderer->tablecell_close();

                // summarize
                if($data['summarize'] && is_numeric($value->getValue())) {
                    if(!isset($this->sums[$colnum])) {
                        $this->sums[$colnum] = 0;
                    }
                    $this->sums[$colnum] += $value->getValue();
                }
            }
            $renderer->tablerow_close();
        }
        $renderer->tabletbody_close();
    }

    /**
     * @param array         $data     the configuration of the table/search
     * @param string        $mode     the mode of the renderer
     * @param Doku_Renderer $renderer the renderer
     */
    private function nullList($data, $mode, Doku_Renderer $renderer) {
        $this->renderPreTable($mode, $renderer, $data);
        $renderer->tablerow_open();
        $renderer->tablecell_open(count($data['cols']) + $data['rownumbers'], 'center');
        $renderer->cdata($this->getLang('none'));
        $renderer->tablecell_close();
        $renderer->tablerow_close();
        $renderer->table_close();
    }
}

// vim:ts=4:sw=4:et:
