<?php

namespace dokuwiki\Ui;

use dokuwiki\Extension\AuthPlugin;
use dokuwiki\Form\Form;

/**
 * DokuWiki User Profile Interface
 *
 * @package dokuwiki\Ui
 */
class UserProfile extends Ui
{
    /**
     * Display the User Profile Form Panel
     *
     * @author   Andreas Gohr <andi@splitbrain.org>
     *
     * @return void
     */
    public function show()
    {
        global $lang;
        global $conf;
        global $INPUT;
        global $INFO;
        /** @var AuthPlugin $auth */
        global $auth;

        // print intro
        print p_locale_xhtml('updateprofile');
        print '<div class="centeralign">';

        $fullname = $INPUT->post->str('fullname', $INFO['userinfo']['name'], true);
        $email = $INPUT->post->str('email', $INFO['userinfo']['mail'], true);

        // create the updateprofile form
        $form = new Form(['id' => 'dw__register']);
        $form->addTagOpen('div')->addClass('no');
        $form->addFieldsetOpen($lang['profile']);
        $form->setHiddenField('do', 'profile');
        $form->setHiddenField('save', '1');

        $attr = array('size' => '50', 'disabled' => 'disabled');
        $input = $form->addTextInput('login', $lang['user'])->attrs($attr)->addClass('edit')
            ->val($INPUT->server->str('REMOTE_USER'));
        $input->getLabel()->attr('class', 'block');
        $form->addHTML("<br>\n");

        $attr = array('size' => '50');
        if (!$auth->canDo('modName')) $attr['disabled'] = 'disabled';
        $input = $form->addTextInput('fullname', $lang['fullname'])->attrs($attr)->addClass('edit')
            ->val($fullname);
        $input->getLabel()->attr('class', 'block');
        $form->addHTML("<br>\n");

        $attr = array('type' => 'email', 'size' =>  '50');
        if (!$auth->canDo('modMail')) $attr['disabled'] = 'disabled';
        $input = $form->addTextInput('email', $lang['email'])->attrs($attr)->addClass('edit')
            ->val($email);
        $input->getLabel()->attr('class', 'block');
        $form->addHTML("<br>\n");

        if ($auth->canDo('modPass')) {
            $attr = array('size'=>'50');
            $input = $form->addPasswordInput('newpass', $lang['newpass'])->attrs($attr)->addClass('edit');
            $input->getLabel()->attr('class', 'block');
            $form->addHTML("<br>\n");

            $input = $form->addPasswordInput('passchk', $lang['passchk'])->attrs($attr)->addClass('edit');
            $input->getLabel()->attr('class', 'block');
            $form->addHTML("<br>\n");
        }

        if ($conf['profileconfirm']) {
            $form->addHTML("<br>\n");
            $attr = array('size' => '50', 'required' => 'required');
            $input = $form->addPasswordInput('oldpass', $lang['oldpass'])->attrs($attr)->addClass('edit');
            $input->getLabel()->attr('class', 'block');
            $form->addHTML("<br>\n");
        }

        $form->addButton('', $lang['btn_save'])->attr('type', 'submit');
        $form->addButton('', $lang['btn_reset'])->attr('type', 'reset');

        $form->addFieldsetClose();
        $form->addTagClose('div');

        print $form->toHTML('UpdateProfile');


        if ($auth->canDo('delUser') && actionOK('profile_delete')) {

            // create the profiledelete form
            $form = new Form(['id' => 'dw__profiledelete']);
            $form->addTagOpen('div')->addClass('no');
            $form->addFieldsetOpen($lang['profdeleteuser']);
            $form->setHiddenField('do', 'profile_delete');
            $form->setHiddenField('delete', '1');

            $form->addCheckbox('confirm_delete', $lang['profconfdelete'])
                ->attrs(['required' => 'required'])
                ->id('dw__confirmdelete')
                ->val('1');

            if ($conf['profileconfirm']) {
                $form->addHTML("<br>\n");
                $attr = array('size' => '50', 'required' => 'required');
                $input = $form->addPasswordInput('oldppass', $lang['oldpass'])->attrs($attr)
                    ->addClass('edit');
                $input->getLabel()->attr('class', 'block');
                $form->addHTML("<br>\n");
            }

            $form->addButton('', $lang['btn_deleteuser'])->attr('type', 'submit');
            $form->addFieldsetClose();
            $form->addTagClose('div');

            print $form->toHTML('ProfileDelete');
        }

        print '</div>';
    }

}
