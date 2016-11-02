<?php

/**
 * TSV export of tabular data
 *
 * @link http://www.iana.org/assignments/media-types/text/tab-separated-values
 */
class renderer_plugin_struct extends Doku_Renderer {

    protected $first = false;

    /**
     * Determine if out put is wanted right now
     *
     * @return bool
     */
    function _doOutput() {
        global $INPUT;

        if(
            !isset($this->info['struct_table_hash']) or
            $this->info['struct_table_hash'] != $INPUT->str('hash')
        ) {
            return false;
        }

        if(!empty($this->info['struct_table_meta'])) {
            return false;
        }

        return true;
    }

    /**
     * Our own format
     *
     * @return string
     */
    function getFormat() {
        return 'struct';
    }

    /**
     * Set proper headers
     */
    function document_start() {
        global $ID;
        $filename = noNS($ID) . '.tsv';
        $headers = array(
            'Content-Type' => 'text/tab-separated-values',
            'Content-Disposition' => 'attachment; filename="' . $filename . '";'
        );
        p_set_metadata($ID, array('format' => array('struct' => $headers)));
        // don't cache
        $this->nocache();
    }


    /**
     * Opening a table row prevents the separator for the first following cell
     */
    function tablerow_open() {
        if(!$this->_doOutput()) return;
        $this->first = true;
    }

    /**
     * Output the delimiter (unless it's the first cell of this row
     *
     * @param int $colspan ignored
     * @param null $align ignored
     * @param int $rowspan ignored
     */
    function tablecell_open($colspan = 1, $align = null, $rowspan = 1) {
        if(!$this->_doOutput()) return;
        if(!$this->first) {
            $this->doc .= "\t";
        }
        $this->first = false;
    }

    /**
     * Alias for tablecell_open
     *
     * @param int $colspan ignored
     * @param null $align ignored
     * @param int $rowspan ignored
     */
    function tableheader_open($colspan = 1, $align = null, $rowspan = 1) {
        if(!$this->_doOutput()) return;
        $this->tablecell_open($colspan, $align, $rowspan);
    }

    /**
     * Add newline at the end of one line
     */
    function tablerow_close() {
        if(!$this->_doOutput()) return;
        $this->doc .= "\n";
    }

    /**
     * Outputs cell content
     *
     * @param string $text
     */
    function cdata($text) {
        if(!$this->_doOutput()) return;
        // FIXME how to handle newlines in TSV??
        $this->doc .= str_replace("\t", '    ', $text); // TSV does not allow tabs in fields
    }

    function internallink($link, $title = null) {
        $this->cdata($title);
    }

}
