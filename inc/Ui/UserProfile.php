<?php

namespace dokuwiki\Ui;

use dokuwiki\Extension\AuthPlugin;
use dokuwiki\Form\Form;
use dokuwiki\JWT;

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
     * @return void
     * @author   Andreas Gohr <andi@splitbrain.org>
     *
     */
    public function show()
    {
        /** @var AuthPlugin $auth */
        global $auth;
        global $INFO;
        global $INPUT;

        $userinfo = [
            'user' => $_SERVER['REMOTE_USER'],
            'name' => $INPUT->post->str('fullname', $INFO['userinfo']['name'], true),
            'mail' => $INPUT->post->str('email', $INFO['userinfo']['mail'], true),

        ];

        echo p_locale_xhtml('updateprofile');
        echo '<div class="centeralign">';

        echo $this->updateProfileForm($userinfo)->toHTML('UpdateProfile');
        echo $this->tokenForm($userinfo['user'])->toHTML();
        if ($auth->canDo('delUser') && actionOK('profile_delete')) {
            echo $this->deleteProfileForm()->toHTML('ProfileDelete');
        }

        echo '</div>';
    }

    /**
     * Add the password confirmation field to the form if configured
     *
     * @param Form $form
     * @return void
     */
    protected function addPasswordConfirmation(Form $form)
    {
        global $lang;
        global $conf;

        if (!$conf['profileconfirm']) return;
        $form->addHTML("<br>\n");
        $attr = ['size' => '50', 'required' => 'required'];
        $input = $form->addPasswordInput('oldpass', $lang['oldpass'])->attrs($attr)
            ->addClass('edit');
        $input->getLabel()->attr('class', 'block');
        $form->addHTML("<br>\n");
    }

    /**
     * Create the profile form
     *
     * @return Form
     */
    protected function updateProfileForm($userinfo)
    {
        global $lang;
        /** @var AuthPlugin $auth */
        global $auth;

        $form = new Form(['id' => 'dw__register']);
        $form->addTagOpen('div')->addClass('no');
        $form->addFieldsetOpen($lang['profile']);
        $form->setHiddenField('do', 'profile');
        $form->setHiddenField('save', '1');

        $attr = ['size' => '50', 'disabled' => 'disabled'];
        $input = $form->addTextInput('login', $lang['user'])
            ->attrs($attr)
            ->addClass('edit')
            ->val($userinfo['user']);
        $input->getLabel()->attr('class', 'block');
        $form->addHTML("<br>\n");

        $attr = ['size' => '50'];
        if (!$auth->canDo('modName')) $attr['disabled'] = 'disabled';
        $input = $form->addTextInput('fullname', $lang['fullname'])
            ->attrs($attr)
            ->addClass('edit')
            ->val($userinfo['name']);
        $input->getLabel()->attr('class', 'block');
        $form->addHTML("<br>\n");

        $attr = ['type' => 'email', 'size' => '50'];
        if (!$auth->canDo('modMail')) $attr['disabled'] = 'disabled';
        $input = $form->addTextInput('email', $lang['email'])
            ->attrs($attr)
            ->addClass('edit')
            ->val($userinfo['mail']);
        $input->getLabel()->attr('class', 'block');
        $form->addHTML("<br>\n");

        if ($auth->canDo('modPass')) {
            $attr = ['size' => '50'];
            $input = $form->addPasswordInput('newpass', $lang['newpass'])->attrs($attr)->addClass('edit');
            $input->getLabel()->attr('class', 'block');
            $form->addHTML("<br>\n");

            $input = $form->addPasswordInput('passchk', $lang['passchk'])->attrs($attr)->addClass('edit');
            $input->getLabel()->attr('class', 'block');
            $form->addHTML("<br>\n");
        }

        $this->addPasswordConfirmation($form);

        $form->addButton('', $lang['btn_save'])->attr('type', 'submit');
        $form->addButton('', $lang['btn_reset'])->attr('type', 'reset');

        $form->addFieldsetClose();
        $form->addTagClose('div');

        return $form;
    }

    /**
     * Create the profile delete form
     *
     * @return Form
     */
    protected function deleteProfileForm()
    {
        global $lang;

        $form = new Form(['id' => 'dw__profiledelete']);
        $form->addTagOpen('div')->addClass('no');
        $form->addFieldsetOpen($lang['profdeleteuser']);
        $form->setHiddenField('do', 'profile_delete');
        $form->setHiddenField('delete', '1');

        $form->addCheckbox('confirm_delete', $lang['profconfdelete'])
            ->attrs(['required' => 'required'])
            ->id('dw__confirmdelete')
            ->val('1');

        $this->addPasswordConfirmation($form);

        $form->addButton('', $lang['btn_deleteuser'])->attr('type', 'submit');
        $form->addFieldsetClose();
        $form->addTagClose('div');
        return $form;
    }

    /**
     * Get the authentication token form
     *
     * @param string $user
     * @return Form
     */
    protected function tokenForm($user)
    {
        global $lang;

        $token = JWT::fromUser($user);

        $form = new Form(['id' => 'dw__profiletoken', 'action' => wl(), 'method' => 'POST']);
        $form->setHiddenField('do', 'authtoken');
        $form->setHiddenField('id', 'ID');
        $form->addFieldsetOpen($lang['proftokenlegend']);
        $form->addHTML('<p>' . $lang['proftokeninfo'] . '</p>');
        $form->addHTML('<p><code style="display: block; word-break: break-word">' . $token->getToken() . '</code></p>');
        $form->addButton('regen', $lang['proftokengenerate']);
        $form->addFieldsetClose();

        return $form;
    }
}
