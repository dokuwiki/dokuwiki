<?php

namespace dokuwiki\Ui;

use dokuwiki\Extension\Event;
use dokuwiki\Form\Form;

/**
 * DokuWiki User Registration Insterface (Register Form)
 *
 * @package dokuwiki\Ui
 */
class UserRegister extends Ui
{
    /**
     * Display the User Registration Form Panel
     *
     * @author   Andreas Gohr <andi@splitbrain.org>
     *
     * @triggers HTML_REGISTERFORM_OUTPUT
     * @return void
     */
    public function show()
    {
        global $lang;
        global $conf;
        global $INPUT;

        $base_attrs = array('size' => '50', 'required' => 'required');
        $email_attrs = $base_attrs + array('type' => 'email');

        // print intro
        print p_locale_xhtml('register');
        print '<div class="centeralign">'.DOKU_LF;

        // create the login form
        $form = new Form(['id' => 'dw__register']);
        $form->addTagOpen('div')->addClass('no');
        $form->addFieldsetOpen($lang['btn_register']);
        $form->setHiddenField('do', 'register');
        $form->setHiddenField('save', '1');

        $input = $form->addTextInput('login', $lang['user'])->attrs($base_attrs)->addClass('edit')
            ->val($INPUT->post->str('login'));
        $input->getLabel()->attr('class', 'block');
        $form->addHTML("<br>\n");

        if (!$conf['autopasswd']) {
            $input = $form->addPasswordInput('pass', $lang['pass'])->attrs($base_attrs)->addClass('edit');
            $input->getLabel()->attr('class', 'block');
            $form->addHTML("<br>\n");
            $input = $form->addPasswordInput('passchk', $lang['passchk'])->attrs($base_attrs)->addClass('edit');
            $input->getLabel()->attr('class', 'block');
            $form->addHTML("<br>\n");
        }

        $input = $form->addTextInput('fullname', $lang['fullname'])->attrs($base_attrs)->addClass('edit')
            ->val($INPUT->post->str('fullname'));
        $input->getLabel()->attr('class', 'block');
        $form->addHTML("<br>\n");

        $input = $form->addTextInput('email', $lang['email'])->attrs($email_attrs)->addClass('edit')
            ->val($INPUT->post->str('email'));
        $input->getLabel()->attr('class', 'block');
        $form->addHTML("<br>\n");

        $form->addButton('', $lang['btn_register'])->attr('type', 'submit');
        $form->addFieldsetClose();
        $form->addTagClose('div');

        // emit HTML_REGISTERFORM_OUTPUT event, print the form
        Event::createAndTrigger('HTML_REGISTERFORM_OUTPUT', $form, 'html_form_output', false);

        print '</div>'.DOKU_LF;
    }

}
