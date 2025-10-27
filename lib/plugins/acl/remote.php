<?php

use dokuwiki\Extension\RemotePlugin;
use dokuwiki\Remote\AccessDeniedException;

/**
 * Class remote_plugin_acl
 */
class remote_plugin_acl extends RemotePlugin
{
    /**
     * Get the list all ACL config entries
     *
     * @return array {Scope: ACL}, where ACL = dictionnary {user/group: permissions_int}
     * @throws AccessDeniedException
     */
    public function listAcls()
    {
        if (!auth_isadmin()) {
            throw new AccessDeniedException(
                'You are not allowed to access ACLs, superuser permission is required',
                114
            );
        }
        /** @var admin_plugin_acl $apa */
        $apa = plugin_load('admin', 'acl');
        $apa->initAclConfig();
        return $apa->acl;
    }

    /**
     * Add a new ACL rule to the config
     *
     * @param string $scope The page or namespace to apply the ACL to
     * @param string $user The user or group to apply the ACL to
     * @param int $level The permission level to set
     * @return bool  If adding the ACL rule was successful
     * @throws AccessDeniedException
     */
    public function addAcl($scope, $user, $level)
    {
        if (!auth_isadmin()) {
            throw new AccessDeniedException(
                'You are not allowed to access ACLs, superuser permission is required',
                114
            );
        }

        /** @var admin_plugin_acl $apa */
        $apa = plugin_load('admin', 'acl');
        return $apa->addOrUpdateACL($scope, $user, $level);
    }

    /**
     * Remove an entry from ACL config
     *
     * @param string $scope The page or namespace the ACL applied to
     * @param string $user The user or group the ACL applied to
     * @return bool If removing the ACL rule was successful
     * @throws AccessDeniedException
     */
    public function delAcl($scope, $user)
    {
        if (!auth_isadmin()) {
            throw new AccessDeniedException(
                'You are not allowed to access ACLs, superuser permission is required',
                114
            );
        }

        /** @var admin_plugin_acl $apa */
        $apa = plugin_load('admin', 'acl');
        return $apa->deleteACL($scope, $user);
    }
}
