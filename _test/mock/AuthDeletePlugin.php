<?php

namespace dokuwiki\test\mock;

/**
 * Class dokuwiki\Plugin\DokuWiki_Auth_Plugin
 */
class AuthDeletePlugin extends AuthPlugin {

    public $loggedOff = false;

    public function __construct($canDeleteUser = true) {
        $this->cando['delUser'] = $canDeleteUser;
    }

    public function checkPass($user, $pass) {
        return $pass == 'password';
    }

    public function deleteUsers($users) {
        return in_array($_SERVER['REMOTE_USER'], $users);
    }

    public function logoff() {
        $this->loggedOff = true;
    }

}