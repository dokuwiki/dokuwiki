<?php

class remote_plugin_acl extends DokuWiki_Remote_Plugin {
	function _getMethods() {
        return array(
            'plugin.acl.addAcl' => array(
                'args' => array('string','string','int'),
                'return' => 'int',
                'name' => 'addAcl',
                'doc' => 'Adds a new ACL rule.'
			), 'plugin.delAcl' => array(
                'args' => array('string','string'),
                'return' => 'int',
                'name' => 'delAcl',
                'doc' => 'Delete an existing ACL rule.'
			),
        );
    }

	
	function addAcl($scope, $user, $level){
		$apa = new admin_plugin_acl(); 
		return $apa->_acl_add($scope, $user, $level);
	}
	
	function delAcl($scope, $user){
		$apa = new admin_plugin_acl(); 
		return $apa->_acl_del($scope, $user);
	}
}

?>
