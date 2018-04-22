<?php

/**
 * Class remote_plugin_acl
 */
class remote_plugin_acl extends DokuWiki_Remote_Plugin {

    /**
     * Returns details about the remote plugin methods
     *
     * @return array Information about all provided methods. {@see RemoteAPI}
     */
    public function _getMethods() {
        return array(
            'listAcls' => array(
                'args' => array(),
                'return' => 'Array of ACLs {scope, user, permission}',
                'name' => 'listAcls',
                'doc' => 'Get the list of all ACLs',
            ),'addAcl' => array(
                'args' => array('string','string','int'),
                'return' => 'int',
                'name' => 'addAcl',
                'doc' => 'Adds a new ACL rule.'
            ), 'delAcl' => array(
                'args' => array('string','string'),
                'return' => 'int',
                'name' => 'delAcl',
                'doc' => 'Delete an existing ACL rule.'
            ),
        );
    }

    /**
     * List all ACL config entries
     *
     * @throws RemoteAccessDeniedException
     * @return dictionary {Scope: ACL}, where ACL = dictionnary {user/group: permissions_int}
     */
    public function listAcls(){
        if(!auth_isadmin()) {
         throw new RemoteAccessDeniedException('You are not allowed to access ACLs, superuser permission is required', 114);
        }
        /** @var admin_plugin_acl $apa */
        $apa = plugin_load('admin', 'acl');
        $apa->_init_acl_config();
        return $apa->acl;
    }

    /**
     * Add a new entry to ACL config
     *
     * @param string $scope
     * @param string $user
     * @param int    $level see also inc/auth.php
     * @throws RemoteAccessDeniedException
     * @return bool
     */
    public function addAcl($scope, $user, $level){
        if(!auth_isadmin()) {
            throw new RemoteAccessDeniedException('You are not allowed to access ACLs, superuser permission is required', 114);
        }

        /** @var admin_plugin_acl $apa */
        $apa = plugin_load('admin', 'acl');
        return $apa->_acl_add($scope, $user, $level);
    }

    /**
     * Remove an entry from ACL config
     *
     * @param string $scope
     * @param string $user
     * @throws RemoteAccessDeniedException
     * @return bool
     */
    public function delAcl($scope, $user){
        if(!auth_isadmin()) {
            throw new RemoteAccessDeniedException('You are not allowed to access ACLs, superuser permission is required', 114);
        }

        /** @var admin_plugin_acl $apa */
        $apa = plugin_load('admin', 'acl');
        return $apa->_acl_del($scope, $user);
    }
}

