<?php

/**
 * Handler for action login
 * 
 * @author Junling Ma <junlingm@gmail.com>
 */
class Doku_Action_Login extends Doku_Action
{
    /**
     * Specifies the action name
     * 
     * @return string (the action name)
     */
    public function action() {
        return "login";
    }

    /**
     * Specifies the required permissions for login
     * 
     * @return string (the permission required)
     */
    public function permission_required() {
        return AUTH_NONE;
    }

    /**
     * Handle 'login'
     * Adapted from act_auth($act) originally written by
     * @author Andreas Gohr <andi@splitbrain.org>
     * 
     * @global string $ID
     * @global array $INFO
     * @global array $conf
     * @return string the next action
     */
    public function handle() {
        global $ID;
        global $INFO;
        global $conf;

        //disable all acl related commands if ACL is disabled
        if (!$conf['useacl']) {
            msg('Command unavailable: '.htmlspecialchars($act),-1);
            return 'show';
        }

        //already logged in?
        if(isset($_SERVER['REMOTE_USER'])){
            return 'show';
        }
    }

    /**
     * Shows the login page
     * Was html_login() by
     * @author   Andreas Gohr <andi@splitbrain.org>
     * 
     * @global string $lang
     * @global array $conf
     * @global string $ID
     * @global array $INPUT
     */
    public function html() {
        global $lang;
        global $conf;
        global $ID;
        global $INPUT;

        print p_locale_xhtml('login');
        print '<div class="centeralign">'.NL;
        $form = new Doku_Form(array('id' => 'dw__login'));
        $form->startFieldset($lang['btn_login']);
        $form->addHidden('id', $ID);
        $form->addHidden('do', 'login');
        $form->addElement(form_makeTextField('u', ((!$INPUT->bool('http_credentials')) ? $INPUT->str('u') : ''), $lang['user'], 'focus__this', 'block'));
        $form->addElement(form_makePasswordField('p', $lang['pass'], '', 'block'));
        if($conf['rememberme']) {
            $form->addElement(form_makeCheckboxField('r', '1', $lang['remember'], 'remember__me', 'simple'));
        }
        $form->addElement(form_makeButton('submit', '', $lang['btn_login']));
        $form->endFieldset();

        if(actionOK('register')){
            $form->addElement('<p>'.$lang['reghere'].': '.tpl_actionlink('register','','','',true).'</p>');
        }

        if (actionOK('resendpwd')) {
            $form->addElement('<p>'.$lang['pwdforget'].': '.tpl_actionlink('resendpwd','','','',true).'</p>');
        }

        html_form('login', $form);
        print '</div>'.NL;
    }
}
