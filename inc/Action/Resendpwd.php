<?php

namespace dokuwiki\Action;

use dokuwiki\Action\Exception\ActionAbort;
use dokuwiki\Action\Exception\ActionDisabledException;

/**
 * Class Resendpwd
 *
 * Handle password recovery
 *
 * @package dokuwiki\Action
 */
class Resendpwd extends AbstractAclAction {

    /** @inheritdoc */
    public function minimumPermission() {
        return AUTH_NONE;
    }

    /** @inheritdoc */
    public function checkPreconditions() {
        parent::checkPreconditions();

        /** @var \dokuwiki\Extension\AuthPlugin $auth */
        global $auth;
        global $conf;
        if(isset($conf['resendpasswd']) && !$conf['resendpasswd']) throw new ActionDisabledException(); //legacy option
        if(!$auth->canDo('modPass')) throw new ActionDisabledException();
    }

    /** @inheritdoc */
    public function preProcess() {
        if($this->resendpwd()) {
            throw new ActionAbort('login');
        }
    }

    /** @inheritdoc */
    public function tplContent() {
        html_resendpwd();
    }

    /**
     * Send a  new password
     *
     * This function handles both phases of the password reset:
     *
     *   - handling the first request of password reset
     *   - validating the password reset auth token
     *
     * @author Benoit Chesneau <benoit@bchesneau.info>
     * @author Chris Smith <chris@jalakai.co.uk>
     * @author Andreas Gohr <andi@splitbrain.org>
     * @fixme this should be split up into multiple methods
     * @return bool true on success, false on any error
     */
    protected function resendpwd() {
        global $lang;
        global $conf;
        /* @var \dokuwiki\Extension\AuthPlugin $auth */
        global $auth;
        global $INPUT;

        if(!actionOK('resendpwd')) {
            msg($lang['resendna'], -1);
            return false;
        }

        $token = preg_replace('/[^a-f0-9]+/', '', $INPUT->str('pwauth'));

        if($token) {
            // we're in token phase - get user info from token

            $tfile = $conf['cachedir'] . '/' . $token[0] . '/' . $token . '.pwauth';
            if(!file_exists($tfile)) {
                msg($lang['resendpwdbadauth'], -1);
                $INPUT->remove('pwauth');
                return false;
            }
            // token is only valid for 3 days
            if((time() - filemtime($tfile)) > (3 * 60 * 60 * 24)) {
                msg($lang['resendpwdbadauth'], -1);
                $INPUT->remove('pwauth');
                @unlink($tfile);
                return false;
            }

            $user = io_readfile($tfile);
            $userinfo = $auth->getUserData($user, $requireGroups = false);
            if(!$userinfo['mail']) {
                msg($lang['resendpwdnouser'], -1);
                return false;
            }

            if(!$conf['autopasswd']) { // we let the user choose a password
                $pass = $INPUT->str('pass');

                // password given correctly?
                if(!$pass) return false;
                if($pass != $INPUT->str('passchk')) {
                    msg($lang['regbadpass'], -1);
                    return false;
                }

                // change it
                if(!$auth->triggerUserMod('modify', array($user, array('pass' => $pass)))) {
                    msg($lang['proffail'], -1);
                    return false;
                }

            } else { // autogenerate the password and send by mail

                $pass = auth_pwgen($user);
                if(!$auth->triggerUserMod('modify', array($user, array('pass' => $pass)))) {
                    msg($lang['proffail'], -1);
                    return false;
                }

                if(auth_sendPassword($user, $pass)) {
                    msg($lang['resendpwdsuccess'], 1);
                } else {
                    msg($lang['regmailfail'], -1);
                }
            }

            @unlink($tfile);
            return true;

        } else {
            // we're in request phase

            if(!$INPUT->post->bool('save')) return false;

            if(!$INPUT->post->str('login')) {
                msg($lang['resendpwdmissing'], -1);
                return false;
            } else {
                $user = trim($auth->cleanUser($INPUT->post->str('login')));
            }

            $userinfo = $auth->getUserData($user, $requireGroups = false);
            if(!$userinfo['mail']) {
                msg($lang['resendpwdnouser'], -1);
                return false;
            }

            // generate auth token
            $token = md5(auth_randombytes(16)); // random secret
            $tfile = $conf['cachedir'] . '/' . $token[0] . '/' . $token . '.pwauth';
            $url = wl('', array('do' => 'resendpwd', 'pwauth' => $token), true, '&');

            io_saveFile($tfile, $user);

            $text = rawLocale('pwconfirm');
            $trep = array(
                'FULLNAME' => $userinfo['name'],
                'LOGIN' => $user,
                'CONFIRM' => $url
            );

            $mail = new \Mailer();
            $mail->to($userinfo['name'] . ' <' . $userinfo['mail'] . '>');
            $mail->subject($lang['regpwmail']);
            $mail->setBody($text, $trep);
            if($mail->send()) {
                msg($lang['resendpwdconfirm'], 1);
            } else {
                msg($lang['regmailfail'], -1);
            }
            return true;
        }
        // never reached
    }

}
