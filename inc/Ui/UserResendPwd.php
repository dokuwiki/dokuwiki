<?php

namespace dokuwiki\Ui;

use dokuwiki\Extension\Event;
use dokuwiki\Form\Form;

/**
 * DokuWiki Resend Password Request Insterface
 *
 * @package dokuwiki\Ui
 */
class UserResendPwd extends Ui
{
    /**
     * Display the form to request a new password for an existing account
     *
     * @author   Benoit Chesneau <benoit@bchesneau.info>
     * @author   Andreas Gohr <andi@splitbrain.org>
     *
     * @triggers HTML_RESENDPWDFORM_OUTPUT
     * @return void
     */
    public function show()
    {
        global $lang;
        global $conf;
        global $INPUT;

        $token = preg_replace('/[^a-f0-9]+/', '', $INPUT->str('pwauth'));

        // print intro
        print p_locale_xhtml('resetpwd');
        print '<div class="centeralign">'.DOKU_LF;

        if (!$conf['autopasswd'] && $token) {
            // create the form
            $form = new Form(['id' => 'dw__resendpwd']);
            $form->addTagOpen('div')->addClass('no');
            $form->addFieldsetOpen($lang['btn_resendpwd']);
            $form->setHiddenField('token', $token);
            $form->setHiddenField('do', 'resendpwd');
            $input = $form->addPasswordInput('pass', $lang['pass'])->attr('size', '50')->addClass('edit');
            $input->getLabel()->attr('class', 'block');
            $form->addHTML("<br>\n");
            $input = $form->addPasswordInput('passchk', $lang['passchk'])->attr('size', '50')->addClass('edit');
            $input->getLabel()->attr('class', 'block');
            $form->addHTML("<br>\n");
            $form->addButton('', $lang['btn_resendpwd'])->attrs(['type' => 'submit']);
            $form->addFieldsetClose();
            $form->addTagClose('div');
        } else {
            // create the form
            $form = new Form(['id' => 'dw__resendpwd']);
            $form->addTagOpen('div')->addClass('no');
            $form->addFieldsetOpen($lang['btn_resendpwd']);
            $form->setHiddenField('do', 'resendpwd');
            $form->setHiddenField('save', '1');
            $form->addHTML("<br>\n");
            $input = $form->addTextInput('login', $lang['user'])->addClass('edit')
                ->val($INPUT->str('login'));
            $input->getLabel()->attr('class', 'block');
            $form->addHTML("<br>\n");
            $form->addHTML("<br>\n");
            $form->addButton('', $lang['btn_resendpwd'])->attrs(['type' => 'submit']);
            $form->addFieldsetClose();
            $form->addTagClose('div');
        }

        // emit HTML_RESENDPWDFORM_OUTPUT event, print the form
        Event::createAndTrigger('HTML_RESENDPWDFORM_OUTPUT', $form, 'html_form_output', false);

        print '</div>'.DOKU_LF;
    }

}
