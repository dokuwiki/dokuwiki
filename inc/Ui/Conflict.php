<?php

namespace dokuwiki\Ui;

use dokuwiki\Extension\Event;
use dokuwiki\Form\Form;

/**
 * DokuWiki Conflict Insterface
 *
 * @package dokuwiki\Ui
 */
class Conflict extends Ui
{
    /**
     * Show warning on conflict detection
     *
     * @author   Andreas Gohr <andi@splitbrain.org>
     *
     * @triggers HTML_CONFLICTFORM_OUTPUT
     * @param string $text
     * @param string $summary
     * @return void
     */
    public function show($text = '', $summary = '')
    {
        global $ID;
        global $lang;

        // print intro
        print p_locale_xhtml('conflict');

        // create the draft form
        $form = new Form(['id' => 'dw__editform']);
        $form->addTagOpen('div')->addClass('no');
        $form->setHiddenField('id', $ID);
        $form->setHiddenField('wikitext', $text);
        $form->setHiddenField('summary', $summary);

        $form->addButton('do[save]', $lang['btn_save'] )->attrs(['type' => 'submit', 'accesskey' => 's']);
        $form->addButton('do[cancel]', $lang['btn_cancel'] )->attrs(['type' => 'submit']);
        $form->addTagClose('div');

        // emit HTML_CONFLICTFORM_OUTPUT event, print the form
        Event::createAndTrigger('HTML_CONFLICTFORM_OUTPUT', $form, 'html_form_output', false);

        print '<br /><br /><br /><br />'.DOKU_LF;
    }

}
