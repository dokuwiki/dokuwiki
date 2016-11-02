<?php

/**
 * CSV export of tabular data
 *
 * @link https://tools.ietf.org/html/rfc4180
 * @link http://csvlint.io/
 */
class renderer_plugin_struct_csv extends Doku_Renderer {

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
        return 'struct_csv';
    }

    /**
     * Set proper headers
     */
    function document_start() {
        global $ID;
        $filename = noNS($ID) . '.csv';
        $headers = array(
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '";'
        );
        p_set_metadata($ID, array('format' => array('struct_csv' => $headers)));
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
     * Output the delimiter (unless it's the first cell of this row) and the text wrapper
     *
     * @param int $colspan ignored
     * @param null $align ignored
     * @param int $rowspan ignored
     */
    function tablecell_open($colspan = 1, $align = null, $rowspan = 1) {
        if(!$this->_doOutput()) return;
        if(!$this->first) {
            $this->doc .= ",";
        }
        $this->first = false;

        $this->doc .= '"';
    }

    /**
     * Close the text wrapper
     */
    function tablecell_close() {
        if(!$this->_doOutput()) return;
        $this->doc .= '"';
    }

    /**
     * Alias for tablecell_open
     *
     * @param int $colspan ignored
     * @param null $align ignored
     * @param int $rowspan ignored
     */
    function tableheader_open($colspan = 1, $align = null, $rowspan = 1) {
        $this->tablecell_open($colspan, $align, $rowspan);
    }

    /**
     * Alias for tablecell_close
     */
    function tableheader_close() {
        $this->tablecell_close();
    }

    /**
     * Add CRLF newline at the end of one line
     */
    function tablerow_close() {
        if(!$this->_doOutput()) return;
        $this->doc .= "\r\n";
    }

    /**
     * Outputs cell content
     *
     * @param string $text
     */
    function cdata($text) {
        if(!$this->_doOutput()) return;
        if($text === '') return;

        $this->doc .= str_replace('"', '""', $text);
    }

    /**
     * Uses cdata to output the title
     *
     * @param string $link
     * @param null $title
     */
    function internallink($link, $title = null) {
        $this->cdata($title);
    }

    /**
     * Uses cdata to output the email address
     *
     * @param string $address
     * @param null $name
     */
    function emaillink($address, $name = null) {
        $this->cdata($address);
    }
}
