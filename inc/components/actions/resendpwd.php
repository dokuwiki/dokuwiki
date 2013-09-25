<?php

/**
 * Handler for action resendpwd
 * 
 * @author Junling Ma <junlingm@gmail.com>
 */
class Doku_Action_Resendpwd extends Doku_Action
{
    /**
     * The Doku_Action interface to specify the action name that this
     * handler can handle.
     * 
     * @return string the action name
     */
    public function action() {
        return "resendpwd";
    }

    /**
     * The Doku_Action interface to specify the required permissions
     * for action resendpwd
     * 
     * @return string the permission required
     */
    public function permission_required() {
        return AUTH_NONE;
    }

    /**
     * Doku_Action interface for handling the resendpwd action
     * 
     * @global array $conf
     * @return string the next action
     */
    public function handle() {
        global $conf;
        //disable all acl related commands if ACL is disabled
        if (!$conf['useacl']) {
            msg('Command unavailable: '.htmlspecialchars($act),-1);
            return 'show';
        }
        if (act_resendpwd()) return "login";
    }

    /**
     * Doku_Action interfce to display the form to request a new password 
     * for an existing account.
     * Was html_resendpwd() by 
     * @author Benoit Chesneau <benoit@bchesneau.info>
     * @author Andreas Gohr <gohr@cosmocode.de>
     * 
     * @global string $lang
     * @global array $conf
     * @global array $INPUT
     */
    public function html() {
        global $lang;
        global $conf;
        global $INPUT;

        $token = preg_replace('/[^a-f0-9]+/','',$INPUT->str('pwauth'));

        if(!$conf['autopasswd'] && $token){
            print p_locale_xhtml('resetpwd');
            print '<div class="centeralign">'.NL;
            $form = new Doku_Form(array('id' => 'dw__resendpwd'));
            $form->startFieldset($lang['btn_resendpwd']);
            $form->addHidden('token', $token);
            $form->addHidden('do', 'resendpwd');

            $form->addElement(form_makePasswordField('pass', $lang['pass'], '', 'block', array('size'=>'50')));
            $form->addElement(form_makePasswordField('passchk', $lang['passchk'], '', 'block', array('size'=>'50')));

            $form->addElement(form_makeButton('submit', '', $lang['btn_resendpwd']));
            $form->endFieldset();
            html_form('resendpwd', $form);
            print '</div>'.NL;
        }else{
            print p_locale_xhtml('resendpwd');
            print '<div class="centeralign">'.NL;
            $form = new Doku_Form(array('id' => 'dw__resendpwd'));
            $form->startFieldset($lang['resendpwd']);
            $form->addHidden('do', 'resendpwd');
            $form->addHidden('save', '1');
            $form->addElement(form_makeTag('br'));
            $form->addElement(form_makeTextField('login', $INPUT->post->str('login'), $lang['user'], '', 'block'));
            $form->addElement(form_makeTag('br'));
            $form->addElement(form_makeTag('br'));
            $form->addElement(form_makeButton('submit', '', $lang['btn_resendpwd']));
            $form->endFieldset();
            html_form('resendpwd', $form);
            print '</div>'.NL;
        }
    }
}
