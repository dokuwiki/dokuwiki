<?php
namespace dokuwiki\plugin\struct\types;

use dokuwiki\plugin\struct\meta\QueryBuilderWhere;

class Wiki extends LongText {

    /**
     * @param int|string $value
     * @param \Doku_Renderer $R
     * @param string $mode
     * @return bool
     */
    public function renderValue($value, \Doku_Renderer $R, $mode) {
        $value = $this->config['prefix'] . $value . $this->config['postfix'];
        $doc = p_render($mode, p_get_instructions($value), $info);
        $R->doc .= $doc; // FIXME this probably does not work for all renderers
        return true;
    }
}
