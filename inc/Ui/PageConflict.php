<?php

namespace dokuwiki\Ui;

use dokuwiki\Extension\Event;
use dokuwiki\Form\Form;

/**
 * DokuWiki Page Conflict Interface
 *
 * @package dokuwiki\Ui
 */
class PageConflict extends Ui
{
    protected $text;
    protected $summary;

    /** 
     * PageConflict Ui constructor
     *
     * @param string $text     wiki text
     * @param string $summary  edit summary
    */
    public function __construct($text = '', $summary = '')
    {
        $this->text    = $text;
        $this->summary = $summary;
    }

    /**
     * Show conflict form to ask whether save anyway or cancel the page edits
     *
     * @author   Andreas Gohr <andi@splitbrain.org>
     *
     * @triggers HTML_CONFLICTFORM_OUTPUT
     * @return void
     */
    public function show()
    {
        global $ID;
        global $lang;

        // print intro
        print p_locale_xhtml('conflict');

        // create the form
        $form = new Form(['id' => 'dw__editform']);
        $form->addTagOpen('div')->addClass('no');
        $form->setHiddenField('id', $ID);
        $form->setHiddenField('wikitext', $this->text);
        $form->setHiddenField('summary', $this->summary);

        $form->addButton('do[save]', $lang['btn_save'] )->attrs(['type' => 'submit', 'accesskey' => 's']);
        $form->addButton('do[cancel]', $lang['btn_cancel'] )->attrs(['type' => 'submit']);
        $form->addTagClose('div');

        // emit HTML_CONFLICTFORM_OUTPUT event, print the form
        Event::createAndTrigger('HTML_CONFLICTFORM_OUTPUT', $form, 'html_form_output', false);

        print '<br /><br /><br /><br />'.DOKU_LF;

        (new Diff($this->text, false))->show();
        print DOKU_LF;
    }

}
