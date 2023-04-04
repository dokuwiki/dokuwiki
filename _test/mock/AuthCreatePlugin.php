<?php

namespace dokuwiki\test\mock;

/**
 * Class dokuwiki\Plugin\DokuWiki_Auth_Plugin
 */
class AuthCreatePlugin extends AuthPlugin {

    public $loggedOff = false;

    /** @var array user cache */
    protected $users = null;

    public function __construct($canAddUser = true) {
        $this->cando['addUser'] = $canAddUser;
    }

    public function checkPass($user, $pass) {
        return $pass == 'password';
    }

    public function createUser($user, $pwd, $name, $mail, $grps = null) {
        if (isset($this->users[$user])) {
            return false;
        }
        $pass = md5($pwd);
        $this->users[$user] = compact('pass', 'name', 'mail', 'grps');
        return true;
    }

    public function logoff() {
        $this->loggedOff = true;
    }

}
