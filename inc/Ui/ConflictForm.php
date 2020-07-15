<?php

namespace dokuwiki\Ui;

use dokuwiki\Extension\Event;
use dokuwiki\Form\Form;

/**
 * DokuWiki Conflict Form
 *
 * @package dokuwiki\Ui
 */
class ConflictForm extends Ui
{
    /**
     * Show conflict form to ask whether save anyway or cancel the page edits
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

        // create the form
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
