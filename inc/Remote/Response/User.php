<?php

namespace dokuwiki\Remote\Response;

/**
 * Represents a user
 */
class User extends ApiResponse
{
    /** @var string The login name of the user */
    public $login;
    /** @var string The full name of the user */
    public $name;
    /** @var string The email address of the user */
    public $mail;
    /** @var array The groups the user is in */
    public $groups;
    /** @var bool Whether the user is a super user */
    public bool $isAdmin;
    /** @var bool Whether the user is a manager */
    public bool $isManager;

    /** @inheritdoc */
    public function __construct($data)
    {
        global $USERINFO;
        global $INPUT;
        $this->login = $INPUT->server->str('REMOTE_USER');
        $this->name = $USERINFO['name'];
        $this->mail = $USERINFO['mail'];
        $this->groups = $USERINFO['grps'];

        $this->isAdmin = auth_isAdmin($this->login, $this->groups);
        $this->isManager = auth_isManager($this->login, $this->groups);
    }
}
