<?php

include_once(DOKU_INC . "/inc/components/action.php");

/**
 * Handler for action register
 * 
 * @author Junling Ma <junlingm@gmail.com>
 */
class Doku_Action_Register extends Doku_Action
{
    /**
     * Specifies the action name
     * 
     * @return string the action name
     */
    public function action() {
        return "register";
    }

    /**
     * Specifies the required permissions for registration.
     * 
     * @return string the permission required
     */
    public function permission_required() {
        return AUTH_NONE;
    }

    /**
     * handle registration
     * 
     * @global array $INPUT
     * @global array $conf
     * @return string the next action
     */
    public function handle() {
        global $INPUT;
        global $conf;
        //disable all acl related commands if ACL is disabled
        if (!$conf['useacl']) {
            msg('Command unavailable: '.htmlspecialchars($act),-1);
            return 'show';
        }
        if ($INPUT->post->bool('save') && register()) return "login";
    }

    /**
     * Print the registration form
     * was html_register() by
     * @author Andreas Gohr <andi@splitbrain.org>
     * 
     * @global string $lang
     * @global array $conf
     * @global array $INPUT
     */
    public function html() {
        global $lang;
        global $conf;
        global $INPUT;

        $base_attrs = array('size'=>50,'required'=>'required');
        $email_attrs = $base_attrs + array('type'=>'email','class'=>'edit');

        print p_locale_xhtml('register');
        print '<div class="centeralign">'.NL;
        $form = new Doku_Form(array('id' => 'dw__register'));
        $form->startFieldset($lang['btn_register']);
        $form->addHidden('do', 'register');
        $form->addHidden('save', '1');
        $form->addElement(form_makeTextField('login', $INPUT->post->str('login'), $lang['user'], '', 'block', $base_attrs));
        if (!$conf['autopasswd']) {
            $form->addElement(form_makePasswordField('pass', $lang['pass'], '', 'block', $base_attrs));
            $form->addElement(form_makePasswordField('passchk', $lang['passchk'], '', 'block', $base_attrs));
        }
        $form->addElement(form_makeTextField('fullname', $INPUT->post->str('fullname'), $lang['fullname'], '', 'block', $base_attrs));
        $form->addElement(form_makeField('email','email', $INPUT->post->str('email'), $lang['email'], '', 'block', $email_attrs));
        $form->addElement(form_makeButton('submit', '', $lang['btn_register']));
        $form->endFieldset();
        html_form('register', $form);

        print '</div>'.NL;
    }
}

