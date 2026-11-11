<?php

namespace dokuwiki\Remote\Response;

use dokuwiki\Remote\AccessDeniedException;

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
    /** @var string[] The groups the user is in */
    public $groups;
    /** @var bool Whether the user is a super user */
    public bool $isadmin;
    /** @var bool Whether the user is a manager */
    public bool $ismanager;

    /**
     * @param string $login defaults to the current user
     * @param string $name
     * @param string $mail
     * @param string[] $groups
     * @throws AccessDeniedException when no user is given and nobody is logged in
     */
    public function __construct($login = '', $name = '', $mail = '', $groups = [])
    {
        global $INPUT;
        global $USERINFO;
        global $auth;

        $this->login = $login;
        $this->name = $name;
        $this->mail = $mail;
        $this->groups = $groups;

        if ($this->login === '') {
            $this->login = $INPUT->server->str('REMOTE_USER');
        }

        if ($this->login === '') {
            throw new AccessDeniedException('No user available', -32604);
        }

        // for current user, use $USERINFO to fill up
        if ($this->login === $INPUT->server->str('REMOTE_USER')) {
            $this->name = $this->name ?: $USERINFO['name'];
            $this->mail = $this->mail ?: $USERINFO['mail'];
            $this->groups = $this->groups ?: $USERINFO['grps'];
        } else {
            // for other users, use auth_getUserData to fill up
            $userData = $auth->getUserData($this->login);
            $this->name = $this->name ?: $userData['name'];
            $this->mail = $this->mail ?: $userData['mail'];
            $this->groups = $this->groups ?: $userData['grps'];
        }

        // check for admin and manager
        $this->isadmin = auth_isAdmin($this->login, $this->groups);
        $this->ismanager = auth_isManager($this->login, $this->groups);
    }

    /** @inheritdoc */
    public function __toString(): string
    {
        return $this->login;
    }
}
