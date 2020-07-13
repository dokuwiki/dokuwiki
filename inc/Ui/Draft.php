<?php

namespace dokuwiki\Ui;

use dokuwiki\Extension\Event;
use dokuwiki\Form\Form;

/**
 * DokuWiki Draft Insterface
 *
 * @package dokuwiki\Ui
 */
class Draft extends Ui
{
    /**
     * Display the Page Draft Form
     * ask the user about how to handle an exisiting draft
     *
     * @author   Andreas Gohr <andi@splitbrain.org>
     *
     * @triggers HTML_DRAFTFORM_OUTPUT
     * @return void
     */
    public function show()
    {
        global $INFO;
        global $ID;
        global $lang;

        $draft = new \dokuwiki\Draft($ID, $INFO['client']);
        $text  = $draft->getDraftText();

        // print intro
        print p_locale_xhtml('draft');

        (new Diff)->show($text, false);

        // create the draft form
        $form = new Form(['id' => 'dw__editform']);
        $form->addTagOpen('div')->addClass('no');
        $form->setHiddenField('id', $ID);
        $form->setHiddenField('date', $draft->getDraftDate());
        $form->setHiddenField('wikitext', $text);

        $form->addTagOpen('div')->id('draft__status');
        $form->addHTML($draft->getDraftMessage());
        $form->addTagClose('div');
        $form->addButton('do[recover]',  $lang['btn_recover'] )->attrs(['type' => 'submit', 'tabindex' => '1']);
        $form->addButton('do[draftdel]', $lang['btn_draftdel'])->attrs(['type' => 'submit', 'tabindex' => '2']);
        $form->addButton('do[show]',     $lang['btn_cancel']  )->attrs(['type' => 'submit', 'tabindex' => '3']);
        $form->addTagClose('div');

        // emit HTML_DRAFTFORM_OUTPUT event, print the form
        Event::createAndTrigger('HTML_DRAFTFORM_OUTPUT', $form, 'html_form_output', false);

        print DOKU_LF;
    }

}
