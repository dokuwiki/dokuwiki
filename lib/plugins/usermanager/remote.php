<?php

use dokuwiki\Extension\AuthPlugin;
use dokuwiki\Extension\RemotePlugin;
use dokuwiki\Remote\AccessDeniedException;
use dokuwiki\Remote\RemoteException;

/**
 * DokuWiki Plugin usermanager (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author Chris Smith <chris@jalakai.co.uk>
 */
class remote_plugin_usermanager extends RemotePlugin
{
    /**
     * Create a new user
     *
     * If no password is provided, a password is auto generated. If the user can't be created
     * by the auth backend a return value of `false` is returned. You need to check this return
     * value rather than relying on the error code only.
     *
     * Superuser permission are required to create users.
     *
     * @param string $user The user's login name
     * @param string $name The user's full name
     * @param string $mail The user's email address
     * @param string[] $groups The groups the user should be in
     * @param string $password The user's password, empty for autogeneration
     * @param bool $notify Whether to send a notification email to the user
     * @return bool Wether the user was successfully created
     * @throws AccessDeniedException
     * @throws RemoteException
     * @todo handle error messages from auth backend
     */
    public function createUser($user, $name, $mail, $groups, $password = '', $notify = false)
    {
        if (!auth_isadmin()) {
            throw new AccessDeniedException('Only admins are allowed to create users', 114);
        }

        /** @var AuthPlugin $auth */
        global $auth;

        if (!$auth->canDo('addUser')) {
            throw new AccessDeniedException(
                sprintf('Authentication backend %s can\'t do addUser', $auth->getPluginName()),
                404
            );
        }

        $user = trim($auth->cleanUser($user));
        $name = trim(preg_replace('/[\x00-\x1f:<>&%,;]+/', '', $name));
        $mail = trim(preg_replace('/[\x00-\x1f:<>&%,;]+/', '', $mail));

        if ($user === '') throw new RemoteException('empty or invalid user', 401);
        if ($name === '') throw new RemoteException('empty or invalid user name', 402);
        if (!mail_isvalid($mail)) throw new RemoteException('empty or invalid mail address', 403);

        if ((string)$password === '') {
            try {
                $password = auth_pwgen($user);
            } catch (\Exception $e) {
                throw new RemoteException('Could not generate password', 405);
            }
        }

        if (!is_array($groups) || $groups === []) {
            $groups = null;
        }

        $ok = (bool)$auth->triggerUserMod('create', [$user, $password, $name, $mail, $groups]);

        if ($ok && $notify) {
            auth_sendPassword($user, $password);
        }

        return $ok;
    }


    /**
     * Remove a user
     *
     * You need to be a superuser to delete users.
     *
     * @param string[] $user The login name of the user to delete
     * @return bool wether the user was successfully deleted
     * @throws AccessDeniedException
     * @todo handle error messages from auth backend
     */
    public function deleteUser($user)
    {
        if (!auth_isadmin()) {
            throw new AccessDeniedException('Only admins are allowed to delete users', 114);
        }

        global $auth;
        if (!$auth->canDo('delUser')) {
            throw new AccessDeniedException(
                sprintf('Authentication backend %s can\'t do delUser', $auth->getPluginName()),
                404
            );
        }

        /** @var AuthPlugin $auth */
        global $auth;
        return (bool)$auth->triggerUserMod('delete', [[$user]]);
    }
}
