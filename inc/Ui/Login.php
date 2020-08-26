<?php

namespace dokuwiki\Ui;

use dokuwiki\Form\Form;

/**
 * DokuWiki User Login Interface (Login Form)
 *
 * @package dokuwiki\Ui
 */
class Login extends Ui
{
    protected $showIcon = false;

    /** 
     * Login Ui constructor
     *
     * @param bool $showIcon  Whether to show svg icons in the register and resendpwd links or not
     */
    public function __construct($showIcon = false)
    {
        $this->showIcon = (bool)$showIcon;
    }

    /**
     * Display the Login Form Panel
     *
     * @author   Andreas Gohr <andi@splitbrain.org>
     *
     * @return void
     */
    public function show()
    {
        global $lang;
        global $conf;
        global $ID;
        global $INPUT;

        // print intro
        print p_locale_xhtml('login');
        print '<div class="centeralign">'.NL;

        // create the login form
        $form = new Form(['id' => 'dw__login', 'action' => wl($ID)]);
        $form->addTagOpen('div')->addClass('no');
        $form->addFieldsetOpen($lang['btn_login']);
        $form->setHiddenField('id', $ID);
        $form->setHiddenField('do', 'login');

        $input = $form->addTextInput('u', $lang['user'])->id('focus__this')->addClass('edit')
            ->val((!$INPUT->bool('http_credentials')) ? $INPUT->str('u') : '');
        $input->getLabel()->attr('class', 'block');
        $form->addHTML("<br>\n");

        $input = $form->addPasswordInput('p', $lang['pass'])->addClass('block edit');
        $input->getLabel()->attr('class', 'block');
        $form->addHTML("<br>\n");

        if ($conf['rememberme']) {
            $form->addCheckbox('r', $lang['remember'])->id('remember__me')->val('1');
        }
        $form->addButton('', $lang['btn_login'])->attr('type', 'submit');
        $form->addFieldsetClose();
        $form->addTagClose('div');

        if(actionOK('register')){
            $registerLink = (new \dokuwiki\Menu\Item\Register())->asHtmlLink('', $this->showIcon);
            $form->addHTML('<p>'.$lang['reghere'].': '. $registerLink .'</p>');
        }

        if (actionOK('resendpwd')) {
            $resendPwLink = (new \dokuwiki\Menu\Item\Resendpwd())->asHtmlLink('', $this->showIcon);
            $form->addHTML('<p>'.$lang['pwdforget'].': '. $resendPwLink .'</p>');
        }

        print $form->toHTML('Login');

        print '</div>';
    }

}
