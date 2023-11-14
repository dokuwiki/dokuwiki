<?php

namespace dokuwiki\Ui;

use dokuwiki\Form\Form;

/**
 * DokuWiki Resend Password Request Interface
 *
 * @package dokuwiki\Ui
 */
class UserResendPwd extends Ui
{
    /**
     * Display the form to request a new password for an existing account
     *
     * @return void
     * @author   Andreas Gohr <andi@splitbrain.org>
     *
     * @author   Benoit Chesneau <benoit@bchesneau.info>
     */
    public function show()
    {
        global $conf;
        global $INPUT;

        $token = preg_replace('/[^a-f0-9]+/', '', $INPUT->str('pwauth'));

        // print intro
        echo p_locale_xhtml('resetpwd');
        echo '<div class="centeralign">';

        if (!$conf['autopasswd'] && $token) {
            $form = $this->formSetNewPassword($token);
        } else {
            $form = $this->formResendPassword();
        }

        echo $form->toHTML('ResendPwd');

        echo '</div>';
    }

    /**
     * create a form ui to set new password
     *
     * @params string $token  cleaned pwauth request variable
     * @return Form
     */
    protected function formSetNewPassword($token)
    {
        global $lang;

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
        $form->addButton('', $lang['btn_resendpwd'])->attr('type', 'submit');
        $form->addFieldsetClose();
        $form->addTagClose('div');
        return $form;
    }

    /**
     * create a form ui to request new password
     *
     * @return Form
     */
    protected function formResendPassword()
    {
        global $lang;

        // create the form
        $form = new Form(['id' => 'dw__resendpwd']);
        $form->addTagOpen('div')->addClass('no');
        $form->addFieldsetOpen($lang['btn_resendpwd']);
        $form->setHiddenField('do', 'resendpwd');
        $form->setHiddenField('save', '1');
        $form->addHTML("<br>\n");

        $input = $form->addTextInput('login', $lang['user'])->addClass('edit');
        $input->getLabel()->attr('class', 'block');
        $form->addHTML("<br>\n");
        $form->addHTML("<br>\n");
        $form->addButton('', $lang['btn_resendpwd'])->attr('type', 'submit');
        $form->addFieldsetClose();
        $form->addTagClose('div');
        return $form;
    }
}
