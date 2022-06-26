<?php
/**
 * The summary XHTML form selects either up to the first two paragraphs
 * it find in a page or the first section (whichever comes first)
 * It strips out the table of contents if one exists
 * Section divs are not used - everything should be nested in a single
 * div with CSS class "page"
 * Headings have their a name link removed and section editing links
 * removed
 * It also attempts to capture the first heading in a page for
 * use as the title of the page.
 *
 *
 * @author Harry Fuecks <hfuecks@gmail.com>
 * @todo   Is this currently used anywhere? Should it?
 */
class Doku_Renderer_xhtmlsummary extends Doku_Renderer_xhtml {

    // Namespace these variables to
    // avoid clashes with parent classes
    protected $sum_paragraphs = 0;
    protected $sum_capture = true;
    protected $sum_inSection = false;
    protected $sum_summary = '';
    protected $sum_pageTitle = false;

    /** @inheritdoc */
    public function document_start() {
        $this->doc .= DOKU_LF.'<div>'.DOKU_LF;
    }

    /** @inheritdoc */
    public function document_end() {
        $this->doc = $this->sum_summary;
        $this->doc .= DOKU_LF.'</div>'.DOKU_LF;
    }

    /** @inheritdoc */
    public function header($text, $level, $pos) {
        if ( !$this->sum_pageTitle ) {
            $this->info['sum_pagetitle'] = $text;
            $this->sum_pageTitle = true;
        }
        $this->doc .= DOKU_LF.'<h'.$level.'>';
        $this->doc .= $this->_xmlEntities($text);
        $this->doc .= "</h$level>".DOKU_LF;
    }

    /** @inheritdoc */
    public function section_open($level) {
        if ( $this->sum_capture ) {
            $this->sum_inSection = true;
        }
    }

    /** @inheritdoc */
    public function section_close() {
        if ( $this->sum_capture && $this->sum_inSection ) {
            $this->sum_summary .= $this->doc;
            $this->sum_capture = false;
        }
    }

    /** @inheritdoc */
    public function p_open() {
        if ( $this->sum_capture && $this->sum_paragraphs < 2 ) {
            $this->sum_paragraphs++;
        }
        parent :: p_open();
    }

    /** @inheritdoc */
    public function p_close() {
        parent :: p_close();
        if ( $this->sum_capture && $this->sum_paragraphs >= 2 ) {
            $this->sum_summary .= $this->doc;
            $this->sum_capture = false;
        }
    }

}


//Setup VIM: ex: et ts=2 :
