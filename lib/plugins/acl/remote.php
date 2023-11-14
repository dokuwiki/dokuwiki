<?php

use dokuwiki\Extension\RemotePlugin;
use dokuwiki\Remote\AccessDeniedException;

/**
 * Class remote_plugin_acl
 */
class remote_plugin_acl extends RemotePlugin
{
    /**
     * Returns details about the remote plugin methods
     *
     * @return array Information about all provided methods. {@see dokuwiki\Remote\RemoteAPI}
     */
    public function _getMethods()
    {
        return [
            'listAcls' => [
                'args' => [],
                'return' => 'Array of ACLs {scope, user, permission}',
                'name' => 'listAcls',
                'doc' => 'Get the list of all ACLs'
            ],
            'addAcl' => [
                'args' => ['string', 'string', 'int'],
                'return' => 'int',
                'name' => 'addAcl',
                'doc' => 'Adds a new ACL rule.'
            ],
            'delAcl' => [
                'args' => ['string', 'string'],
                'return' => 'int',
                'name' => 'delAcl',
                'doc' => 'Delete an existing ACL rule.'
            ]
        ];
    }

    /**
     * List all ACL config entries
     *
     * @throws AccessDeniedException
     * @return dictionary {Scope: ACL}, where ACL = dictionnary {user/group: permissions_int}
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
     * Add a new entry to ACL config
     *
     * @param string $scope
     * @param string $user
     * @param int    $level see also inc/auth.php
     * @throws AccessDeniedException
     * @return bool
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
     * @param string $scope
     * @param string $user
     * @throws AccessDeniedException
     * @return bool
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
