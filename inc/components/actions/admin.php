<?php

class Doku_Action_Admin extends Doku_Action
{
	public function action() { return "admin"; }

	public function permission_required() {
		global $INFO;
		// if the manager has the needed permissions for a certain admin
		// action is checked later
        if ($INFO['ismanager']) return AUTH_READ;
        return AUTH_ADMIN;
	}
	
	public function handle() {
		global $INPUT;
		global $INFO;
		global $conf;
		//disable all acl related commands if ACL is disabled
		if (!$conf['useacl']) {
			msg('Command unavailable: '.htmlspecialchars($act),-1);
			return 'show';
		}
		// retrieve admin plugin name from $_REQUEST['page']
		if (($page = $INPUT->str('page', '', true)) != '') {
			$pluginlist = plugin_list('admin');
			if (in_array($page, $pluginlist)) {
				// attempt to load the plugin
				if ($plugin =& plugin_load('admin',$page) !== null){
					/** @var DokuWiki_Admin_Plugin $plugin */
					if($plugin->forAdminOnly() && !$INFO['isadmin']){
						// a manager tried to load a plugin that's for admins only
						$INPUT->remove('page');
						msg('For admins only',-1);
					}else{
						$plugin->handle();
					}
				}
			}
		}
    }
}