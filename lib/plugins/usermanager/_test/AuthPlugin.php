<?php

namespace dokuwiki\plugin\usermanager\test;

/**
 * Simple Auth Plugin for testing
 *
 * All users are stored in a simple array
 * @todo This might be useful for other tests and could replace the remaining mock auth plugins
 */
class AuthPlugin extends \dokuwiki\Extension\AuthPlugin {

    public $loggedOff = false;

    /** @var array user storage */
    public $users = [];

    /** @inheritdoc */
    public function __construct($cando = []) {
        parent::__construct(); // for compatibility

        // our own default capabilities
        $this->cando['addUser'] = true;
        $this->cando['delUser'] = true;

        // merge in given capabilities for testing
        $this->cando = array_merge($this->cando, $cando);
    }

    /** @inheritdoc */
    public function createUser($user, $pwd, $name, $mail, $grps = null) {
        if (isset($this->users[$user])) {
            return false;
        }
        $pass = md5($pwd);
        $grps = (array) $grps;
        $this->users[$user] = compact('pass', 'name', 'mail', 'grps');
        return true;
    }

    /** @inheritdoc */
    public function deleteUsers($users)
    {
        $deleted = 0;
        foreach ($users as $user) {
            if (isset($this->users[$user])) {
                unset($this->users[$user]);
                $deleted++;
            }

        }
        return $deleted;
    }
}
