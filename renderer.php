<?php

/**
 * TSV export of tabular data
 *
 * @link http://www.iana.org/assignments/media-types/text/tab-separated-values
 */
class renderer_plugin_struct extends Doku_Renderer {

    protected $first  = false;
    protected $output = false;

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
     * Decide if this is the table we want to export
     *
     * @param null $maxcols ignored
     * @param null $numrows ignored
     * @param null $pos ignored
     */
    function table_open($maxcols = null, $numrows = null, $pos = null) {

        global $INPUT;

        if(
            isset($this->info['struct_table_hash']) and
            $this->info['struct_table_hash'] == $INPUT->str('hash')
        ) {
            $this->output = true;
        } else {
            $this->output = false;
        }
    }

    /**
     * Stop output after table for sure
     *
     * @param null $pos ignored
     */
    function table_close($pos = null) {
        $this->output = false;
    }

    /**
     * Opening a table row prevents the separator for the first following cell
     */
    function tablerow_open() {
        if(!$this->output) return;
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
        if(!$this->output) return;
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
        if(!$this->output) return;
        $this->tablecell_open($colspan, $align, $rowspan);
    }

    /**
     * Add newline at the end of one line
     */
    function tablerow_close() {
        if(!$this->output) return;
        $this->doc .= "\n";
    }

    /**
     * Outputs cell content
     *
     * @param string $text
     */
    function cdata($text) {
        if(!$this->output) return;
        // FIXME how to handle newlines in TSV??
        $this->doc .= str_replace("\t", '    ', $text); // TSV does not allow tabs in fields
    }

    function internallink($link, $title = null) {
        $this->cdata($title);
    }

}
