<?php

class remote_plugin_acl extends DokuWiki_Remote_Plugin {
    function _getMethods() {
        return array(
            'addAcl' => array(
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

    function addAcl($scope, $user, $level){
        $apa = plugin_load('admin', 'acl');
        return $apa->_acl_add($scope, $user, $level);
    }

    function delAcl($scope, $user){
        $apa = plugin_load('admin', 'acl');
        return $apa->_acl_del($scope, $user);
    }
}

