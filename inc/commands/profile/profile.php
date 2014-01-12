<?php

include_once(dirname(__FILE__).'/profile_common.php');

/**
 * Handler for action profile
 * 
 * @author Junling Ma <junlingm@gmail.com>
 */
class Doku_Action_Profile extends Doku_Action_Profile_Common
{
    /**
     * Specifies the action name
     * 
     * @return string the action name
     */
    public function action() {
        return "profile";
    }

    /**
     * update profile
     * 
     * @return string the next action
     */
    public function handle() {
        $act = parent::handle();
        if ($act) return $act;
        if (updateprofile()) {
            msg($lang['profchanged'],1);
            return 'show';
        }
    }
}

/**
 * Renderer for action profile
 * 
 * @author Junling Ma <junlingm@gmail.com>
 */
class Doku_Action_Renderer_Profile extends Doku_Action_Renderer
{
    /**
     * Specifies the action name
     * 
     * @return string the action name
     */
    public function action() {
        return "profile";
    }

    /**
     * displaying the update profile form
     * Was html_updateprofile() by
     * @author Christopher Smith <chris@jalakai.co.uk>
     * @author Andreas Gohr <andi@splitbrain.org>
     * 
     * @global array $lang
     * @global array $conf
     * @global Input $INPUT
     * @global array $INFO
     * @global string $auth
     */
    public function xhtml(){
        global $lang;
        global $conf;
        global $INPUT;
        global $INFO;
        /** @var auth_basic $auth */
        global $auth;

        print p_locale_xhtml('updateprofile');
        print '<div class="centeralign">'.NL;

        $fullname = $INPUT->post->str('fullname', $INFO['userinfo']['name'], true);
        $email = $INPUT->post->str('email', $INFO['userinfo']['mail'], true);
        $form = new Doku_Form(array('id' => 'dw__register'));
        $form->startFieldset($lang['profile']);
        $form->addHidden('do', 'profile');
        $form->addHidden('save', '1');
        $form->addElement(form_makeTextField('login', $_SERVER['REMOTE_USER'], $lang['user'], '', 'block', array('size'=>'50', 'disabled'=>'disabled')));
        $attr = array('size'=>'50');
        if (!$auth->canDo('modName')) $attr['disabled'] = 'disabled';
        $form->addElement(form_makeTextField('fullname', $fullname, $lang['fullname'], '', 'block', $attr));
        $attr = array('size'=>'50', 'class'=>'edit');
        if (!$auth->canDo('modMail')) $attr['disabled'] = 'disabled';
        $form->addElement(form_makeField('email','email', $email, $lang['email'], '', 'block', $attr));
        $form->addElement(form_makeTag('br'));
        if ($auth->canDo('modPass')) {
            $form->addElement(form_makePasswordField('newpass', $lang['newpass'], '', 'block', array('size'=>'50')));
            $form->addElement(form_makePasswordField('passchk', $lang['passchk'], '', 'block', array('size'=>'50')));
        }
        if ($conf['profileconfirm']) {
            $form->addElement(form_makeTag('br'));
            $form->addElement(form_makePasswordField('oldpass', $lang['oldpass'], '', 'block', array('size'=>'50', 'required' => 'required')));
        }
        $form->addElement(form_makeButton('submit', '', $lang['btn_save']));
        $form->addElement(form_makeButton('reset', '', $lang['btn_reset']));

        $form->endFieldset();
        html_form('updateprofile', $form);

        if ($auth->canDo('delUser') && actionOK('profile_delete')) {
            $form_profiledelete = new Doku_Form(array('id' => 'dw__profiledelete'));
            $form_profiledelete->startFieldset($lang['profdeleteuser']);
            $form_profiledelete->addHidden('do', 'profile_delete');
            $form_profiledelete->addHidden('delete', '1');
            $form_profiledelete->addElement(form_makeCheckboxField('confirm_delete', '1', $lang['profconfdelete'],'dw__confirmdelete','', array('required' => 'required')));
            if ($conf['profileconfirm']) {
                $form_profiledelete->addElement(form_makeTag('br'));
                $form_profiledelete->addElement(form_makePasswordField('oldpass', $lang['oldpass'], '', 'block', array('size'=>'50', 'required' => 'required')));
            }
            $form_profiledelete->addElement(form_makeButton('submit', '', $lang['btn_deleteuser']));
            $form_profiledelete->endFieldset();

            html_form('profiledelete', $form_profiledelete);
        }

        print '</div>'.NL;
    }
}
