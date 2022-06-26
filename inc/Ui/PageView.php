<?php

namespace dokuwiki\Ui;

use dokuwiki\Extension\Event;

/**
 * DokuWiki PageView Interface
 *
 * @package dokuwiki\Ui
 */
class PageView extends Ui
{
    protected $text;

    /** 
     * PageView Ui constructor
     *
     * @param null|string $text  wiki text or null for showing $ID
     */
    public function __construct($text = null)
    {
        $this->text = $text;
    }

    /**
     * Show a wiki page
     *
     * @author   Andreas Gohr <andi@splitbrain.org>
     *
     * @triggers HTML_SHOWREV_OUTPUT
     * @return void
     */
    public function show()
    {
        global $ID;
        global $REV;
        global $HIGH;
        global $INFO;
        global $DATE_AT;

        //disable section editing for old revisions or in preview
        if ($this->text !== null || $REV) {
            $secedit = false;
        } else {
            $secedit = true;
        }

        if ($this->text !== null) {
            //PreviewHeader
            echo '<br id="scroll__here" />';

            // print intro for preview
            echo p_locale_xhtml('preview');
            echo '<div class="preview"><div class="pad">';
            $html = html_secedit(p_render('xhtml', p_get_instructions($this->text), $info), $secedit);
            if ($INFO['prependTOC']) $html = tpl_toc(true) . $html;
            echo $html;
            echo '<div class="clearer"></div>';
            echo '</div></div>';

        } else {
            if ($REV || $DATE_AT) {
                // print intro for old revisions
                $data = array('rev' => &$REV, 'date_at' => &$DATE_AT);
                Event::createAndTrigger('HTML_SHOWREV_OUTPUT', $data, [$this, 'showrev']);
            }
            $html = p_wiki_xhtml($ID, $REV, true, $DATE_AT);
            $html = html_secedit($html, $secedit);
            if ($INFO['prependTOC']) $html = tpl_toc(true) . $html;
            $html = html_hilight($html, $HIGH);
            echo $html;
        }
    }

    /**
     * Show a revision warning
     *
     * @author Szymon Olewniczak <dokuwiki@imz.re>
     */
    public function showrev()
    {
        print p_locale_xhtml('showrev');
    }


}
